import apiRequest from './apiRequest'
import formatDate from './formatDate'

/**
 * Since both the explore and subscriptions pages hit the same endpoint but
 * with different parameters, it was worth abstracting shared logic into its
 * own utility function.
 * 
 * @param {string[]} additionalParams 
 * @returns Promise containing returned posts with their authors and tags.
 */
function getPosts(additionalParams = []) {
    const params = [
        'include=author,tags',
        'fields[users]=username',
        'sort=-createdAt',
        ...additionalParams,
    ]

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
                        .find(({ type, id }) => type === 'users' && id === related.author)
                        .attributes
                        .username,

                    tags: included
                        .filter(({ type, id }) => type === 'tags' && related.tags.includes(id))
                        .map(tag => tag.attributes.text),
                })
            }

            return items
        })
}

export default getPosts