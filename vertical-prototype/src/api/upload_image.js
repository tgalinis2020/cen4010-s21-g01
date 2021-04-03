export default function upload_image(file) {
    return fetch(
        `https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php/upload`,
        { method: 'POST', body: file }
    )
}
