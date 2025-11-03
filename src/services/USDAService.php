<?php
/**
 * USDA FoodData Central API Service
 * Provides nutrition information lookups from USDA database
 */
class USDAService {
    private $api_key;
    private $base_url = 'https://api.nal.usda.gov/fdc/v1';
    
    public function __construct($api_key = null) {
        $this->api_key = $api_key ?? USDA_API_KEY;
    }
    
    /**
     * Search for foods in USDA database
     * @param string $query Search term
     * @param int $page_size Number of results (default 10, max 50)
     * @return array|false Search results or false on error
     */
    public function searchFoods($query, $page_size = 10) {
        $url = $this->base_url . '/foods/search';
        
        $params = [
            'api_key' => $this->api_key,
            'query' => $query,
            'pageSize' => min($page_size, 50),
            'dataType' => 'Survey (FNDDS),Foundation,SR Legacy' // Most comprehensive data types
        ];
        
        $response = $this->makeRequest($url, $params);
        
        // Check if response is an error
        if (is_array($response) && isset($response['error'])) {
            return $response; // Return error array
        }
        
        if ($response && isset($response['foods'])) {
            return $response['foods'];
        }
        
        return false;
    }
    
    /**
     * Get detailed nutrition information for a specific food
     * @param int $fdc_id USDA FoodData Central ID
     * @return array|false Food details or false on error
     */
    public function getFoodDetails($fdc_id) {
        $url = $this->base_url . '/food/' . $fdc_id;
        
        $params = [
            'api_key' => $this->api_key,
            'format' => 'full'
        ];
        
        return $this->makeRequest($url, $params);
    }
    
    /**
     * Make HTTP request to USDA API
     * @param string $url API endpoint URL
     * @param array $params Query parameters
     * @return array|false Response data or false on error
     */
    private function makeRequest($url, $params = []) {
        $url_with_params = $url . '?' . http_build_query($params);
        
        // Use cURL for better error handling
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_with_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error || $http_code !== 200) {
            $error_message = "USDA API Error: HTTP $http_code";
            
            // Check for rate limit errors
            if ($http_code === 429) {
                $error_message = "API rate limit exceeded. Please wait before making more requests.";
                if ($this->api_key === 'DEMO_KEY') {
                    $error_message .= " DEMO_KEY has low limits (30/hour, 50/day). Get a free API key for 1000/hour at https://fdc.nal.usda.gov/api-key-signup.html";
                }
            } elseif ($http_code === 403) {
                $error_message = "API access forbidden. Your API key may be invalid or suspended.";
            } elseif ($error) {
                $error_message .= " - $error";
            }
            
            error_log($error_message);
            return ['error' => $error_message, 'http_code' => $http_code];
        }
        
        $data = json_decode($response, true);
        return $data ?? false;
    }
    
    /**
     * Convert metric units to imperial
     * @param float $value Metric value
     * @param string $unit Metric unit
     * @return array ['value' => converted_value, 'unit' => imperial_unit]
     */
    private function convertToImperial($value, $unit) {
        switch(strtolower($unit)) {
            case 'g':
                // Grams to ounces
                return ['value' => round($value * 0.035274, 2), 'unit' => 'oz'];
            case 'mg':
                // Milligrams to milligrams (keep same for small values)
                // Or convert to grains for larger values
                if ($value >= 100) {
                    return ['value' => round($value * 0.015432, 2), 'unit' => 'gr'];
                }
                return ['value' => $value, 'unit' => 'mg'];
            case 'µg':
            case 'ug':
                // Micrograms - keep same (too small to convert meaningfully)
                return ['value' => $value, 'unit' => 'µg'];
            case 'kcal':
                // Calories stay the same
                return ['value' => $value, 'unit' => 'kcal'];
            default:
                // Unknown unit, return as-is
                return ['value' => $value, 'unit' => $unit];
        }
    }
    
    /**
     * Format nutrition data for display
     * @param array $food_data USDA food data
     * @param string $unit_system 'metric' or 'imperial'
     * @return array Formatted nutrition information
     */
    public function formatNutritionData($food_data, $unit_system = 'metric') {
        if (!isset($food_data['foodNutrients'])) {
            return [];
        }
        
        $nutrition = [
            'name' => $food_data['description'] ?? 'Unknown',
            'brand' => $food_data['brandOwner'] ?? null,
            'serving_size' => $food_data['servingSize'] ?? null,
            'serving_unit' => $food_data['servingSizeUnit'] ?? null,
            'portions' => [],
            'nutrients' => []
        ];
        
        // Extract food portions if available
        if (isset($food_data['foodPortions']) && is_array($food_data['foodPortions'])) {
            foreach ($food_data['foodPortions'] as $portion) {
                $modifier = $portion['modifier'] ?? $portion['portionDescription'] ?? '';
                $amount = $portion['gramWeight'] ?? $portion['amount'] ?? null;
                $measureUnit = $portion['measureUnit']['name'] ?? '';
                
                if ($modifier || $measureUnit) {
                    $portionInfo = [];
                    if ($amount && $measureUnit) {
                        $portionInfo['description'] = trim($amount . ' ' . $measureUnit . ' ' . $modifier);
                    } else if ($modifier) {
                        $portionInfo['description'] = $modifier;
                    } else if ($measureUnit) {
                        $portionInfo['description'] = $measureUnit;
                    }
                    
                    if (isset($portion['gramWeight'])) {
                        $portionInfo['gram_weight'] = $portion['gramWeight'];
                    }
                    
                    if (!empty($portionInfo)) {
                        $nutrition['portions'][] = $portionInfo;
                    }
                }
            }
        }
        
        // Map of important nutrients to extract
        $nutrient_map = [
            'Energy' => ['id' => 1008, 'unit' => 'kcal'],
            'Protein' => ['id' => 1003, 'unit' => 'g'],
            'Total lipid (fat)' => ['id' => 1004, 'unit' => 'g'],
            'Carbohydrate, by difference' => ['id' => 1005, 'unit' => 'g'],
            'Fiber, total dietary' => ['id' => 1079, 'unit' => 'g'],
            'Sugars, total including NLEA' => ['id' => 2000, 'unit' => 'g'],
            'Sodium, Na' => ['id' => 1093, 'unit' => 'mg'],
            'Cholesterol' => ['id' => 1253, 'unit' => 'mg'],
            'Fatty acids, total saturated' => ['id' => 1258, 'unit' => 'g'],
            'Vitamin D (D2 + D3)' => ['id' => 1114, 'unit' => 'µg'],
            'Calcium, Ca' => ['id' => 1087, 'unit' => 'mg'],
            'Iron, Fe' => ['id' => 1089, 'unit' => 'mg'],
            'Potassium, K' => ['id' => 1092, 'unit' => 'mg']
        ];
        
        // Extract nutrients
        foreach ($food_data['foodNutrients'] as $nutrient) {
            $nutrient_name = $nutrient['nutrient']['name'] ?? null;
            $nutrient_id = $nutrient['nutrient']['id'] ?? null;
            
            // Check if this is one of our tracked nutrients
            foreach ($nutrient_map as $key => $info) {
                if ($nutrient_id === $info['id']) {
                    $amount = $nutrient['amount'] ?? 0;
                    $unit = $nutrient['nutrient']['unitName'] ?? $info['unit'];
                    
                    // Convert to imperial if requested
                    if ($unit_system === 'imperial' && $unit !== 'kcal') {
                        $converted = $this->convertToImperial($amount, $unit);
                        $amount = $converted['value'];
                        $unit = $converted['unit'];
                    }
                    
                    $nutrition['nutrients'][$key] = [
                        'value' => $amount,
                        'unit' => $unit
                    ];
                    break;
                }
            }
        }
        
        return $nutrition;
    }
}
?>
