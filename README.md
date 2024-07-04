SIMPLE PIC PAY:

After cloning the project, you need to follow these steps:

Install Composer dependencies

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

The project is using SAIL (https://laravel.com/docs/11.x/sail), below are the main commands:

- To upload the project containers
  ```sh
    ./vendor/bin/sail up -d
    ```
- To bring down
  ```sh
    ./vendor/bin/sail down
    ```
- Access container
  ```sh
    ./vendor/bin/sail shell
    ```

------------------------------------

After upload the project containers run these commands:

- To create tables in database
  ```sh
    ./vendor/bin/sail artisan migrate
    ```

- To seed database
  ```sh
    ./vendor/bin/sail artisan db:seed
    ```

- To create a queue worker
  ```sh
    ./vendor/bin/sail artisan queue:work
    ```

------------------------------------
If everything is correct, you can now interact with the REST API via port 80 in your local environment. There is already
a user to log in email: admin@gmail.com, password: password.

Here's a link from postman collection: https://www.postman.com/lunar-module-saganist-20405784/workspace/public/collection/17258694-f8a5ed3d-3e42-4d14-8cfb-d9647391094b?action=share&creator=17258694

The routes are :

LOGIN:

```sh
POST api/login

{
  "email": "admin@gmail.com",
  "password": "password"
}

Response
{
    "access_token": "1|Wn4fyCTSqkS5n3SdNwPsdzAdHYzgUULn5sWj4pSF784a0ea5"
}
```

--------------------------------------
USER:

```sh
POST api/users

The response normally is status 201.
The document_number can be sent with or without punctuation marks
The user type will be defined from document_number lentgh,
 if it's 14 the type is 2 = shop owner, if it's 11 it's 1 = customer.

Response
{
    "name": "Specter",
    "email": "shopowner@gmail.com",
    "password": "asdfasdf",
    "document_number": "94.471.890/0001-63"
}
```

```sh
GET api/users?page=1&per_page=1

Bearer token required in header
Page and per page are not required, the default value is page = 1, per page = 10.
The response normally is status 200.

Response
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "Specter",
            "type": 1,
            "email": "specter@gmail.com",
            "created_at": "2024-07-03 05:25:35",
            "updated_at": "2024-07-03 05:25:35"
        }
    ],
    "first_page_url": "http://localhost/api/users?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost/api/users?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://localhost/api/users?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "http://localhost/api/users",
    "per_page": 1,
    "prev_page_url": null,
    "to": 1,
    "total": 1
}
```

```sh
GET api/users/1

Bearer token required in header
The response normally is status 200.

Response
{
    "id": 1,
    "name": "Specter",
    "type": 1,
    "email": "specter@gmail.com",
    "created_at": "2024-07-03 05:25:35",
    "updated_at": "2024-07-03 05:25:35"
}
```

--------------------------------------
TRANSACTION:
```sh
POST api/users/transactions

Bearer token required in header
The authenticated user is the payer always,
 and a shop owner cannot pay a transaction.

In the value field, the last two numbers are decimals,
 minimum is 1(a cent) and maximum is 100000000(a million).

Response
{
    "receiver_id": 2,
    "value": 1000
}
```

```sh
GET api/users/transactions?page=1&per_page=1

Bearer token required in header
Page and per page are not required, the default value is page = 1, per page = 10.
The response normally is status 200.

Response
{
    "current_page": 1,
    "data": [
        {
            "id": 10,
            "payer_id": 9,
            "receiver_id": 10,
            "value": 1000,
            "status": 2,
            "completed_at": "2024-07-04 03:36:22",
            "created_at": "2024-07-04 03:36:22",
            "updated_at": "2024-07-04 03:36:22"
        }
    ],
    "first_page_url": "http://localhost/api/users/transactions?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost/api/users/transactions?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://localhost/api/users/transactions?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "http://localhost/api/users/transactions",
    "per_page": 1,
    "prev_page_url": null,
    "to": 1,
    "total": 1
}
```

```sh
GET api/users/transactions/1

Bearer token required in header
The response normally is status 200.

Response
{
    "id": 10,
    "payer_id": 9,
    "receiver_id": 10,
    "value": 1000,
    "status": 2,
    "completed_at": "2024-07-04 03:36:22",
    "created_at": "2024-07-04 03:36:22",
    "updated_at": "2024-07-04 03:36:22"
}
```
