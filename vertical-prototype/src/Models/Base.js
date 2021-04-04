import apiRequest from '../utils/apiRequest'

export default class Base
{
    constructor(obj = {}) {
        this.id = obj.id || null
        this.attributes = obj.attributes || {}
        this.dirtyAttributes = []
        this.relationships = obj.relationships || {}
    }

    // Child classes must override this method!
    get type() {
        return 'generic'
    }

    hydrate(obj) {
        this.id = obj.id || null
        this.attributes = obj.attributes || {}
        this.relationships = obj.relationships || {}

        return this
    }

    setAttribute(attr, val) {
        this.dirtyAttributes.push(attr)
        this.attributes[attr] = val

        return this
    }

    getAttribute(attr) {
        return this.attributes[attr]
    }

    toResourceIdentifier() {
        return {
            type: this.type,
            id:   this.id,
        }
    }

    toResource() {
        return {
            type:       this.type,
            id:         this.id,
            attributes: this.attributes,
        }
    }

    create(relationships = null) {
        const attributes = {}

        for (const a of this.dirtyAttributes) {
            attributes[a] = this.attributes[a]
        }

        const payload = { type: this.type, attributes }

        if (relationships !== null) {
            payload['relationships'] = relationships
        }

        console.log(payload)

        return apiRequest('POST', `/${this.type}`, payload)
            .then(res => {
                this.dirtyAttributes = []

                return res.json()
            })
            .then(res => res.data)
    }

    update() {
        const attributes = {}

        for (const a of this.dirtyAttributes) {
            attributes[a] = this.attributes[a]
        }

        const payload = { type: this.type, id: this.id, attributes }

        return apiRequest('PATCH', `/${this.type}/${this.id}`, payload)
            .then(() => {
                this.dirtyAttributes = []

                return this
            })
    }

    delete() {
        return apiRequest('DELETE', `/${this.type}/${this.id}`);
    }

    updateRelationship(method, relationship, payload) {
        return apiRequest(
            method,
            `/${this.type}/${this.id}/relationships/${relationship}`,
            payload
        )
    }

    updateToManyRelationship(method, relationship, objs) {
        return this.updateRelationship(
            method,
            relationship,
            objs.map(obj => obj.toResourceIdentifier())
        )
    }

    updateToOneRelationship(method, relationship, obj) {
        return this.updateRelationship(
            method,
            relationship,
            obj.toResourceIdentifier()
        )
    }
}