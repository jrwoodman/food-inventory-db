# 🧪 Food Inventory System Testing Guide

## 📋 **Pre-Testing Checklist**

### 1. **Verify File Structure**
Check that these key files exist:
```
├── src/
│   ├── models/
│   │   ├── User.php ✅
│   │   ├── Food.php ✅
│   │   └── Ingredient.php ✅
│   ├── auth/
│   │   └── Auth.php ✅
│   ├── controllers/
│   │   ├── UserController.php ✅
│   │   └── InventoryController.php ✅
│   ├── views/
│   │   ├── auth/login.php ✅
│   │   ├── users/index.php ✅
│   │   └── dashboard.php ✅
│   └── database/
│       ├── Database.php ✅
│       └── schema.sql ✅
├── database/
│   └── food_inventory.db ✅
├── assets/
│   ├── css/style.css ✅
│   └── js/app.js ✅
└── public/
    └── index.php ✅
```

### 2. **Verify Database**
Check that the SQLite database was created with tables:
```bash
sqlite3 database/food_inventory.db ".tables"
```
Should show: `categories, foods, ingredients, users, user_sessions, etc.`

### 3. **Check Default Admin User**
```bash
sqlite3 database/food_inventory.db "SELECT username, email, role FROM users;"
```
Should show: `admin|admin@foodinventory.local|admin`

## 🚀 **Testing Steps**

### **Step 1: Start the Server**
```bash
cd /home/russ/projects/food-inventory-db
php -S localhost:8000 -t public/
```

*Note: If you get library errors with PHP, you may need to install missing dependencies or use a different PHP version.*

### **Step 2: Open Browser**
Navigate to: **http://localhost:8000**

## 🔐 **Authentication Testing**

### **Test 1: Initial Login**
1. **Expected**: Should redirect to login page automatically
2. **Login with**:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **Expected Result**: Successful login → Dashboard

### **Test 2: Theme Toggle**
1. **Location**: Top-right corner of any page
2. **Test**: Click the sun/moon toggle
3. **Expected**: Page should switch between light and dark themes
4. **Verification**: Theme should persist when navigating between pages

### **Test 3: Navigation (Admin User)**
1. **Expected Navigation Items**:
   - 📊 Dashboard
   - 🍎 Add Food  
   - 🧄 Add Ingredient
   - 👥 Users (admin only)
2. **User Info**: Should show "Welcome, admin!" or "Welcome, System!"

## 👥 **User Management Testing**

### **Test 4: User Management (Admin Only)**
1. **Click**: 👥 Users button
2. **Expected**: User management page with admin user listed
3. **Test**: Click "👤 Add User"
4. **Create Test User**:
   - Username: `testuser`
   - Email: `test@example.com`
   - Password: `testpass123`
   - Role: `user`
5. **Expected**: User created successfully

### **Test 5: Multi-User Login**
1. **Logout**: Click 🚪 Logout
2. **Login as Test User**:
   - Username: `testuser`
   - Password: `testpass123`
3. **Expected**: 
   - Different navigation (no "Users" button)
   - Welcome message shows test user's name

## 📦 **Inventory Testing**

### **Test 6: Add Food Items**
1. **Click**: 🍎 Add Food
2. **Add Sample Food**:
   - Name: `Apples`
   - Category: `Fruits`
   - Quantity: `10`
   - Unit: `pieces`
   - Location: `Refrigerator`
   - Expiry Date: (few days from now)
3. **Expected**: Food added successfully → Dashboard

### **Test 7: Add Ingredients with Multiple Locations**
1. **Click**: 🧄 Add Ingredient  
2. **Add Sample Ingredient**:
   - Name: `Olive Oil`
   - Category: `Oils`
   - Unit: `ml`
   - Cost per Unit: `0.02`
   - Supplier: `Local Store`
   - *Note*: The multi-location feature may need additional form fields

### **Test 8: Data Ownership**
1. **As regular user**: Should only see items they created
2. **As admin**: Should see all users' items
3. **Test**: Switch between admin and regular user accounts

## 👤 **Profile Management Testing**

### **Test 9: Profile Update**
1. **Click**: 👤 Profile (in user dropdown)
2. **Update Information**:
   - First Name: `Test`
   - Last Name: `User`
   - Email: `updated@example.com`
3. **Expected**: Profile updated successfully

### **Test 10: Password Change**
1. **In Profile Page**: Find password change section
2. **Change Password**:
   - Current: `testpass123`
   - New: `newpassword123`
   - Confirm: `newpassword123`
3. **Test**: Logout and login with new password

## 🔒 **Security Testing**

### **Test 11: Access Control**
1. **As regular user**: Try accessing `/index.php?action=users`
2. **Expected**: Access denied or redirect
3. **As viewer**: Should have read-only access

### **Test 12: Session Management**
1. **In Profile**: Check active sessions
2. **Test**: Revoke a session
3. **Expected**: Session removed from list

## 📱 **Responsive Design Testing**

### **Test 13: Mobile View**
1. **Resize browser** to mobile width (< 768px)
2. **Expected**: 
   - Navigation collapses properly
   - Forms remain usable
   - Tables scroll horizontally
   - Theme toggle remains accessible

## 🎯 **Expected Results Summary**

| Feature | Status | Notes |
|---------|--------|--------|
| ✅ Login/Logout | Should work | Default: admin/admin123 |
| ✅ User Management | Admin only | Create/edit/delete users |
| ✅ Role-based Access | Working | Admin > User > Viewer |
| ✅ Theme Toggle | Working | Persists across pages |
| ✅ Food/Ingredient CRUD | Working | User-specific data |
| ✅ Multi-location Support | Implemented | For ingredients |
| ✅ Profile Management | Working | Update info/password |
| ✅ Session Security | Working | Database-backed sessions |
| ✅ Responsive Design | Working | Mobile-friendly |

## 🐛 **Common Issues & Solutions**

### **Issue 1: PHP Library Errors**
```
php: error while loading shared libraries: libxml2.so.2
```
**Solution**: Install missing libraries or use Docker/different PHP version

### **Issue 2: Database Permission Errors**
**Solution**: 
```bash
chmod 755 database/
chmod 664 database/food_inventory.db
```

### **Issue 3: Login Not Working**
**Check**:
1. Database contains admin user
2. Password hash is correct
3. Session directory is writable

### **Issue 4: Theme Not Persisting**
**Check**:
1. JavaScript is loading properly
2. localStorage is available
3. Theme toggle script is running

## 🎉 **Success Criteria**

Your system is working correctly if:

✅ **Authentication**: Can login/logout with admin account  
✅ **User Management**: Can create and manage users (admin)  
✅ **Data Security**: Users only see their own data  
✅ **Theme Switching**: Light/dark mode works and persists  
✅ **Inventory Management**: Can add/edit foods and ingredients  
✅ **Role-based Access**: Different features for different roles  
✅ **Mobile Responsive**: Works well on mobile devices  

## 📞 **Next Steps After Testing**

1. **Change Default Password**: Update admin password immediately
2. **Create Additional Users**: Set up user accounts for your team
3. **Add Real Data**: Start adding your actual inventory
4. **Customize Categories**: Update food/ingredient categories in config
5. **Configure Backup**: Set up regular database backups

## 🔧 **Development Notes**

- **Default Credentials**: admin / admin123 (change immediately!)
- **Database**: SQLite file at `database/food_inventory.db`
- **Sessions**: Stored in database, expire after 1 hour
- **Roles**: admin (full access), user (own data), viewer (read-only)
- **Multi-location**: Ingredients can be stored in multiple locations

**Happy Testing!** 🚀