@echo off
echo --- GET categories ---
curl -s "http://localhost:8000/backend/api.php?resource=categories"
echo.
echo --- GET menus ---
curl -s "http://localhost:8000/backend/api.php?resource=menus"
echo.
echo --- REGISTER ---
curl -s -X POST "http://localhost:8000/backend/api.php?resource=users&action=register" -H "Content-Type: application/json" -d "{\"name\":\"Test User\",\"email\":\"api_test@example.com\",\"password\":\"secret123\",\"role\":\"customer\"}"
echo.
echo --- LOGIN ---
curl -s -X POST "http://localhost:8000/backend/api.php?resource=users&action=login" -H "Content-Type: application/json" -d "{\"email\":\"api_test@example.com\",\"password\":\"secret123\"}"
echo.
echo --- CREATE CATEGORY ---
curl -s -X POST "http://localhost:8000/backend/api.php?resource=categories" -H "Content-Type: application/json" -d "{\"name\":\"Makanan\",\"description\":\"Kategori makanan\"}"
echo.
echo --- CREATE MENU ---
curl -s -X POST "http://localhost:8000/backend/api.php?resource=menus" -H "Content-Type: application/json" -d "{\"name\":\"Nasi Uduk\",\"description\":\"Enak\",\"price\":15000}"
echo.
echo --- GET menus after create ---
curl -s "http://localhost:8000/backend/api.php?resource=menus"
echo.
echo --- CREATE ORDER (using first user and first menu) ---
curl -s -X POST "http://localhost:8000/backend/api.php?resource=orders" -H "Content-Type: application/json" -d "{\"user_id\":1,\"stand_id\":null,\"total\":30000,\"items\":[{\"menu_id\":1,\"quantity\":2,\"price\":15000}]}"
echo.
echo --- GET orders ---
curl -s "http://localhost:8000/backend/api.php?resource=orders"
echo.
echo --- CREATE PAYMENT ---
curl -s -X POST "http://localhost:8000/backend/api.php?resource=payments" -H "Content-Type: application/json" -d "{\"order_id\":1,\"amount\":30000,\"method\":\"cash\",\"status\":\"paid\"}"
echo.
echo --- GET payments ---
curl -s "http://localhost:8000/backend/api.php?resource=payments"
echo.
echo --- TEST COMPLETE ---
