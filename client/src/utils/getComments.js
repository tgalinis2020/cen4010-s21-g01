import apiRequest from "./apiRequest"
import formatDate from "./formatDate"

function getComments(id) {
    const params = [
        'include=author',
        'fields[users]=username,avatar',
        'sort=-createdAt',
    ]

    return apiRequest('GET', `/posts/${id}/comments?${params.join('&')}`)
        .then((res) => res.json())
        .then(({ data, included }) => data.map(({ id, attributes, relationships }) =>({
            id,
            text: attributes.text,
            createdAt: formatDate(attributes.createdAt),
            author: included
                .find(({ id }) => id === relationships.author.data.id)
                .attributes,
        })))
}

export default getComments
