import User from '../../Models/User'

function getSession() {
    return Promise.resolve({
        user: new User({
            id: '1',
            attributes: {
                username: 'DummyUser123',
                firstName: 'Dummy',
                lastName: 'User',
                email: 'tgalinis2020@fau.edu',
                avatar: 'https://i.imgur.com/l3e1XuO.jpeg',
            }
        }),
    
        subscriptions: [],
    })
}

export default getSession
