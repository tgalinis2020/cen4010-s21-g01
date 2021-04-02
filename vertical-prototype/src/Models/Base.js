import api_request from '../api/api_request'
import convert_datetime from '../api/convert_datetime'

export default class Base
{
    constructor(type) {
        this.id = null
        this.type = type
        this.attributes = {}
        this.relationships = {}
    }

    hydrate(obj) {
        this.id = obj.id
        this.attributes = obj.attributes

        if ('createdAt' in this.attributes) {
            this.attributes.createdAt = convert_datetime(this.attributes.createdAt)
        }

        this.relationships = obj.relationships
    }

    setAttribute(attr, val) {
        this.attributes[attr] = val
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
        const payload = {
            type:       this.type,
            attributes: this.attributes
        }

        if (relationships !== null) {
            payload['relationships'] = relationships
        }

        return api_request('POST', `/${this.type}`, payload)
    }

    update() {
        return api_request('PATCH', `/${this.type}`, {
            type:       this.type,
            id:         this.id,
            attributes: this.attributes,
        })
    }

    delete() {
        return api_request('DELETE', `/${this.type}/${this.id}`);
    }

    updateRelationship(method, relationship, payload) {
        return api_request(
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