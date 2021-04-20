import apiRequest from "./apiRequest"
import formatDate from "./formatDate"

function getPost(postId) {
    const params = [
        'include=author,tags,pets',
        'fields[users]=username,avatar',
        'fields[pets]=name,avatar',
    ]

    return apiRequest('GET', `/posts/${postId}?${params.join('&')}`)
        .then((res) => res.json())
        .then(({ data, included }) => {
            const { id, attributes, relationships } = data
            const { image, title, text, createdAt } = attributes
            
            const related = {
                author: relationships.author.data.id,
            }

            // Pluck the IDs from to-many relationships, if applicable.
            for (const key of ['tags', 'pets', 'likes']) {
                related[key] = key in relationships
                    ? relationships[key].data.map(({ id }) => id)
                    : []
            }

            return {
                id,
                image,
                title,
                text,
                createdAt: formatDate(createdAt),

                author: included
                    .find(({ type, id }) => type === 'users' && id === related.author)
                    .attributes,

                tags: included
                    .filter(({ type, id }) => type === 'tags' && related.tags.includes(id))
                    .map(({ attributes }) => attributes.text),

                pets: included
                    .filter(({ type, id }) => type === 'pets' && related.pets.includes(id))
                    .map(({ id, attributes }) => ({ id, name: attributes.name, avatar: attributes.avatar })),
            }
        })
}

export default getPost