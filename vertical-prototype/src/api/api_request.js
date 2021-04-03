export default function api_request(method, target, payload = null) {
    const url = `https://lamp.cse.fau.edu/~cen4010_s21_g01${target}`
    const init = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
        }
    }

    if (['POST', 'PUT', 'PATCH'].includes(method)) {
        init['body'] = payload
    }

    return fetch(url, init).then(r => r.json())
}
