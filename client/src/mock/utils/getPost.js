function getPost(id) {
    return Promise.resolve({
        id,
        image: 'https://i.imgur.com/uDCyg1E.jpeg',
        title: 'Doggo',
        text: 'Cute doggy :)',
        createdAt: formatDate('2021-04-11 12:30:00'),
        author: 'DummyUser',
        tags: ['cute', 'dog', 'aww'],
        pets: [
            { id: '1', name: 'Mr. Meow', avatar: null },
            { id: '2', name: 'Bean', avatar: null },
        ],
    })
}

export default getPost
