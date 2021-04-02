export default function api_request(method, target, payload = null) {
    return fetch(`https://lamp.cse.fau.edu/~cen4010_s21_g01${target}`, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
        },
        body: JSON.stringify({ data: payload }),
    }).then(r => r.json())
}
