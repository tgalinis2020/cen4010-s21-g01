export default function upload_image(file) {
    return fetch(`https://lamp.cse.fau.edu/~cen4010_s21_g01/uploads`, { method: 'POST', body: file })
        .then(r => r.json())
        .then(r => r.data)
}
