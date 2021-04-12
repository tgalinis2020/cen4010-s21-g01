import apiRequest from './apiRequest'
import formatDate from './formatDate'

/**
 * Fetches 12 posts from the API in descending order.
 * Starts from the provided cursor, if applicable.
 */
function getPosts(cursor = null) {
    const params = [
        'include=author,tags',
        'fields[users]=username',
        'sort=-createdAt',
        'page[size]=12',
    ]

    if (cursor) {
        params.push(`page[before]=${cursor}`)
    }

    return apiRequest('GET', `/posts?${params.join('&')}`)
        .then((res) => res.json())
        .then((res) => res.data)
        .then(({ data, included }) => {
            const items = []

            for (const { id, attributes, relationships } of data) {
                
                // TODO:    Pets and likes should be here as well but they are
                //          not represented in the front-end yet.
                // Note that some relationships might not be available, such as
                // tags.
                const related = {
                    author: relationships.author.data.id,
                    tags: 'tags' in relationships ? relationships.tags.data.map(({ id }) => id) : [],
                }

                items.push({
                    id,
                    image: attributes.image,
                    title: attributes.title,
                    text: attributes.text,
                    createdAt: formatDate(attributes.createdAt),

                    // Posts MUST have an author so it should be safe to assume
                    // that the find method returns a resource object of
                    // type "users".
                    author: included
                        .find(obj => obj.type === 'users' && obj.id === related.author)
                        .attributes
                        .username,

                    tags: included
                        .filter(obj => obj.type === 'tags' && related.tags.includes(obj.id))
                        .map(tag => `#${tag.attributes.text}`),
                })
            }

            return items
        })
}

export default getPosts