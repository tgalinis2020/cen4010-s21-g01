function getComments(postId) {
    return Promise.resolve([
        {
            text: 'Aww, such a good boi!',
            createdAt: formatDate('2021-04-12 06:00:23'),
            author: 'blahblah123',
        },

        {
            text: 'Precious.',
            createdAt: formatDate('2021-04-12 08:21:42'),
            author: 'ilovepets99',
        }
    ])
}

export default getComments
