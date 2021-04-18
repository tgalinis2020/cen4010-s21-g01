import apiRequest from "./apiRequest"

function getPets(userId) {
    return apiRequest('GET', `/users/${userId}/pets`)
        .then((res) => res.json())
        .then(({ data }) => data.map(({ id, attributes }) => ({
            id,
            image: attributes.image,
            name: attributes.name,
            isChecked: false
        })))
}

export default getPets