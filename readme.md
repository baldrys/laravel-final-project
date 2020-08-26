# Апи для интернет магазина на laravel.

Документация по роутам (swagger):

### API Docs v1:
+ [Swagger API Docs - VIEW](https://generator.swagger.io/?url=https://raw.githubusercontent.com/baldrys/laravel-final-project/master/swagger-api.yaml)
+ [Swagger API Docs - EDIT](https://editor.swagger.io/?url=https://raw.githubusercontent.com/baldrys/laravel-final-project/master/swagger-api.yaml)

### Типы пользователей
1. Admin
2. Store user
3. Customer

## Список таблиц

1. users - full_name (string), api_token (string), role (enum - Customer(default), Store user, Admin)

2. items (блюда) - store_id, name(string)

3. item_ingredient (ингредиенты) - store_id, name(string), price(в USD - float, 2 знака после запятой)

4. item_ingredients (связка блюда - ингредиенты) - item_id, ingredient_id, amount

5. cart_items - user_id, item_id, amount

6. orders - store_id, customer_id(user id), status (enum) = [Canceled, Placed (default), Approved, Shipped, Received], total_price

7. stores - name (string)

8. store_users (сотрудники магазина) - store_id, user_id

9. order_items (товары заказа) order_id, item_id, amount


