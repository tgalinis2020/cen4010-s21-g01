/**
 * If the response's status code does not match the one provided, the inner
 * function will throw an error.
 * 
 * @param {number} status 
 * @returns 
 */
 function convertToJsonOrThrowError(status) {
    return function (res) {
        if (res.status !== status) {
            throw res.status
        }

        return res.json()
    }
}

export default convertToJsonOrThrowError