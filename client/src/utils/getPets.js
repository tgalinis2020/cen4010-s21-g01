import apiRequest from "./apiRequest"

function getPets(userId) {
    return apiRequest('GET', `/users/${userId}/pets`)
        .then((res) => res.json())
        .then(({ data }) => data.map(({ id, attributes }) => ({
            id,
            name: attributes.name,
            avatar: attributes.avatar,
        })))
}

export default getPets