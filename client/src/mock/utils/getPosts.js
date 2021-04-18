function getPosts(maxPosts = null, additionalParams = []) {
    const item = {
        id: '1',
        image: 'https://i.imgur.com/uDCyg1E.jpeg',
        title: 'Doggo',
        text: 'Cute doggy :)',
        createdAt: formatDate('2021-04-11 12:30:00'),

        // Posts MUST have an author so it should be safe to assume
        // that the find method returns a resource object of
        // type "users".
        author: 'DummyUser',
        tags: ['cute', 'dog', 'aww'],
    }

    const items = []
    const numPosts = maxPosts ?? 24

    for (let i = 0; i < numPosts; ++i) {
        items.push(item)
    }

    return Promise.resolve(items)
}

export default getPosts