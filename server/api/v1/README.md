# The Pet Park API

This application conforms to the [JSON:API version 1.0 specification](https://jsonapi.org/format/#fetching-pagination).
With the exception of the `/upload` endpoint, all input data must be
serialized in JSON and be wrapped inside of the `data` namespace when making a
request to an endpoint that receives input from the request body.

```javascript
{
    "data": {
        /* input goes here */
    }
}
```

Some request body parameters are nullable. To denote this, their data type
will be prefixed with a question mark (?).

Likewise when fetching data, the returned information is contained inside the
`data` namespace of the response.
If querying a collection, data will be an array rather than an object.


### POST /upload

This endpoint doesn't accept JSON data as input; it handles multipart forms.
When a user uploads a post or changes a profile picture,
the provided image asset must be uploaded beforehand.

Query Parameters: `empty`

Request Parameters: `empty`

Body Parameters:

* `file: PNG|JPG|GIF` The image to upload


Returns:

* `201` if the image was uploaded successfully. The response body will contain
  the "image" key with a URL pointing to the uploaded image.

* `401` if the user is not authenticated.



## The Session Endpoint

This endpoint handles all authentication-related tasks.


### GET /session

Since HttpOnly cookies cannot be read by JavaScript, this endpoint lets the
client application read the authenticated user's data.

Query Parameters: `empty`

Request Parameters: `empty` 

Body Parameters: `empty`

Returns:

* `200` if the session cookie is set. Response body contains the following
  fields in the `data` namespace: `username`, `email`, `firstName`, `lastName`,
  and `avatar`.

* `401` if session cookie is not set.


### POST /session

Authenticates a user based on their username/e-mail and password combination.
If the provided details are correct, an HttpOnly cookie is set within the site's
domain containing a JSON Web Token with claims about the user.

Most resource endpoints require that this cookie is set to perform actions
that can mutate the data model.

Query Parameters: `empty`

Request Parameters: `empty`

Body Parameters:

* `username: string` Although the parameter is called "username", one could also
  use their e-mail to log in.

* `password: string` User's password. Used to compare with the hash stored in
  the database.

Returns:

* `200` on successful login. The HttpOnly session cookie is set with
  corresponding claims.

* `403` if the user is authenticated.

* `404` if the provided username or e-mail was not found or the provided
  password's hash did not match the entry in the database.


### DELETE /session

If the session token from logging in is set, this endpoint simply unsets the
cookie.

Query Parameters: `empty`

Request Parameters: `empty`

Body Parameters: `empty`

Returns:

* `200` if session token was found and unset.

* `401` if the user isn't logged in.


## Resource Endpoints

This section is still a work in progress.

### GET /users

Get a collection of registered users.

Request Parameters: `empty`

Body Parameters: `empty`

Returns:

* `200`


### GET /users/:user_id

Get a user associated with the given ID.

Request Parameters:

* `user_id: string` The user's ID.

Body Parameters: `empty`

Returns:

* `200` if user exists

* `404` if if user does not exist


### POST /users

Creates a new user account with the provided information in the request body.
Username and e-mail must be unique. Since only authenticated users can post
images, the avatar cannot be provided on account registration.

Query Parameters: `empty`

Request Parameters: `empty`

Attributes:

* `username: string` The user's username. It is displayed in posts and comments.
  Must be unique.

* `email: string` The user's e-mail address. Must be unique.

* `password: string` The user's password. Note: it is not stored in the database
  verbatim!

* `firstName: string` The user's first name.

* `lastName: string` The user's last name.

Returns:

* `201` on account creation.

* `400` if any required attributes are missing.

* `409` if username and/or e-mail are already registered.


### GET /users/:user_id/pets

Similar to `GET /pets` with the exception that all of the returned pets are from the
specified user.

Request Parameters:

* `user_id: string` The user's ID.

Returns: `see /pets`


### GET /users/:user_id/subscriptions

Similar to `/pets` with the exception that all of the returned pets were
subscribed to by the user.

### GET /users/:user_id/posts

Similar to `/posts` with the exception that all of the returned posts are from
the specified user.

Request Parameters:

* `user_id: string` The user's ID.

Returns: `see /posts`


### POST /pets

Creates a new pet and maps it to the user. User's ID in the request parameter
must match with their ID in the session token.

Request Parameters: `empty`

Attributes:
 
- `name: string`: The pet's name.
- `type: string`: The type of pet. 
- `breed: string`: The pet's breed.

Relationships:

- `owner: users`: The owner of the pet.

### POST /users/:user_id/relationships/subscriptions

Adds a subscription to the user's list of subscriptions.

Request Parameters:

- `user_id: string` The user's ID

### POST /users/:user_id/subscriptions/:pet_id

Adds a pet to the user's subscriptions.


### DELETE /users/:user_id/subscriptions/:pet_id

Removes a pet from the user's subscriptions.



### POST /users/:id/posts

Creates a new post with the provided user as the author.


### GET /users/:user_id/favorites

Similar to `/posts` with the exception that all of the returned posts are the
user's favorites.


### POST /users/:user_id/favorites/:post_id

Adds a post to the user's favorites.


### DELETE /users/:user_id/favorites/:post_id

Removes a post from the user's favorites.


### GET /posts

Fetches a collection of posts from the database. By default, the posts
are sorted by ID in descending order.


### POST /posts

Creates a new post. Only authenticated users can create a post: their session
token is used to derive the post author.


### GET /posts/:post_id/comments

TODO


### POST /posts/:post_id/comments

Creates a new comment associated with the post. Only authenticated users
can create comments: their session token is used to derive the comment author.


### GET /pets

TODO
