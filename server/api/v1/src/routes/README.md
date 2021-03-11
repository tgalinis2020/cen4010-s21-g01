# API endpoints with supported HTTP methods

When making a request to an endpoint that receives input from the request
body, all of the input data is wrapped inside of the `data` namespace.

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



### GET /search

Searches the database for users, pets, and posts.

Query String Parameters:

* `q: String` The search terms. Posts can be queried by prepending the string
  with a hashtag (#). "#fish" would search for posts tagged with the
  "fish" keyword. Users can be searched by prepending their username with the
  at-symbol (@). "@bob" would search for users that contain "bob" in their
  username. Pets can be searched by prepending the term with an exclamation
  point (!). "!spike" would search for pets called Spike. These filters can
  be combined to narrow results, "@bob !spike" will search for a pet named
  Spike whose owner is Bob. If no symbols are prepended to the search term,
  it is assumed that it is either a tag or a pet's name.

Body: `empty`

Returns:

* `200` on successful lookup. Request body contains relevant collections of
  data indexed by `users`, `pets` and `posts`.

* `404` if search yielded no results.


### POST /upload

This endpoint doesn't accept JSON data as input; it handles multipart forms.
When a user uploads a post or changes a profile picture,
the provided image asset must be uploaded beforehand.

Query String Parameters: `empty`

Body:

* `file: PNG|JPG|GIF` The image to upload


Returns:

* `201` if the image was uploaded successfully. The response body will contain
  the "image" key with a URL pointing to the uploaded image.

* `401` if the user is not authenticated.



## Authentication Endpoints

### POST /auth/login

Authenticates a user based on their username/e-mail and password combination.
If the provided details are correct, an HttpOnly cookie is set within the site's
domain containing a JSON Web Token with claims about the user.

All resource endpoints require that this cookie is set to perform actions
that can mutate the data model.

Query String Parameters: `empty`

Body:

* `username: String` Although the parameter is called "username", one could also
  use their e-mail to log in.

* `password: String` User's password. Used to compare with the hash stored in
  the database.

Returns:

* `200` on successful login. The HttpOnly session cookie is set with
  corresponding claims.

* `403` if the user is authenticated.

* `404` if the provided username or e-mail was not found or the provided
  password's hash did not match the entry in the database.


### POST /auth/logout

If the session token from logging in is set, this endpoint simply unsets the
cookie.

Query Parameters: `empty`

Body: `empty`

Returns:

* `200` if session token was found and unset.

* `401` if the user isn't logged in


### POST /auth/register

Creates a new user account with the provided information in the request body.
Username and e-mail must be unique. Since only authenticated users can post
images, the avatar cannot be provided on account registration.

Query Parameters: `empty`

Body:

* `username: String` The user's username. It is displayed in posts and comments.
  Must be unique.

* `email: String` The user's e-mail address. Must be unique.

* `password: String` The user's password. Note: it is not stored in the database
  verbatim!

* `firstName: String` The user's first name.

* `lastName: String` The user's last name.

Returns:

* `201` on account creation.

* `400` if any body parameters are missing.

* `409` if username and/or e-mail are already registered.


## Resource Endpoints

TODO
