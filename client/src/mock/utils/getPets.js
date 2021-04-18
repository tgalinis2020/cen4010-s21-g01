function getPets(userId) {
    return Promise.resolve([
        { id: '1', image: null, name: 'Bean',     isChecked: false },
        { id: '2', image: null, name: 'Charlie',  isChecked: false },
        { id: '3', image: null, name: 'Mr. Meow', isChecked: false },
    ])
}

export default getPets