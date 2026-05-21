Set-Location 'C:\Users\user\CaFood_Web'
try { $r = Invoke-WebRequest 'http://localhost:8000/backend/api.php?resource=categories' -UseBasicParsing; Write-Output 'CATEGORIES:'; Write-Output $r.Content } catch { Write-Output 'ERROR CATEGORIES' }
try { $r = Invoke-WebRequest 'http://localhost:8000/backend/api.php?resource=menus' -UseBasicParsing; Write-Output 'MENUS:'; Write-Output $r.Content } catch { Write-Output 'ERROR MENUS' }
try { $r = Invoke-WebRequest -Uri 'http://localhost:8000/backend/api.php?resource=users&action=register' -Method POST -Body (ConvertTo-Json @{name='Test User'; email='test@example.com'; password='secret123'; role='customer'}) -ContentType 'application/json' -UseBasicParsing; Write-Output 'REGISTER:'; Write-Output $r.Content } catch { Write-Output 'ERROR REGISTER' }
try { $r = Invoke-WebRequest -Uri 'http://localhost:8000/backend/api.php?resource=users&action=login' -Method POST -Body (ConvertTo-Json @{email='test@example.com'; password='secret123'}) -ContentType 'application/json' -UseBasicParsing; Write-Output 'LOGIN:'; Write-Output $r.Content } catch { Write-Output 'ERROR LOGIN' }
try { $r = Invoke-WebRequest -Uri 'http://localhost:8000/backend/api.php?resource=menus' -Method POST -Body (ConvertTo-Json @{name='Nasi Goreng'; description='Nasi goreng enak'; price=20000}) -ContentType 'application/json' -UseBasicParsing; Write-Output 'CREATE MENU:'; Write-Output $r.Content } catch { Write-Output 'ERROR CREATE MENU' }
try {
  $users = Invoke-RestMethod 'http://localhost:8000/backend/api.php?resource=users' -Method GET
  $menus = Invoke-RestMethod 'http://localhost:8000/backend/api.php?resource=menus' -Method GET
  $uid = $users[0].id
  $mid = $menus[0].id
  $orderBody = @{user_id = $uid; stand_id = $null; total = 40000; items = @(@{menu_id=$mid;quantity=2;price=20000})}
  $r = Invoke-WebRequest -Uri 'http://localhost:8000/backend/api.php?resource=orders' -Method POST -Body (ConvertTo-Json $orderBody -Depth 5) -ContentType 'application/json' -UseBasicParsing
  Write-Output 'CREATE ORDER:'; Write-Output $r.Content
} catch { Write-Output 'ERROR CREATE ORDER' }
