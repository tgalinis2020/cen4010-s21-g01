function uploadImage(file) {
    const url = `https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php/upload`
    const body = new FormData()

    body.append('data', file, file.name)

    return fetch(url, { method: 'POST', body })
}

export default uploadImage
