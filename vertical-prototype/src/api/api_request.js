export default function api_request(method, target, data = null) {
    const url = `https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php${target}`
    const init = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
        }
    }

    if (['POST', 'PUT', 'PATCH'].includes(method)) {
        init['body'] = JSON.stringify({ data })
    }

    return fetch(url, init)
}
