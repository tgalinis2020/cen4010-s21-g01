import { useState } from 'react'

import debounce from '../utils/debouce'

/**
 * A custom hook to ease the form validation process.
 * 
 * Validators is an object where the key corresponds to a form field
 * and the value is an array of validation functions.
 * 
 * Each validation function must return a promise that itself returns
 * a validation message. If the validation message is null, the field is
 * considered valid.
 * 
 * Although it may be inconvenient, promises are used because some validation
 * strategies involve HTTP requests (e.g. checking if a username already exists).
 * For validation that is immediately resolved, return the error message using
 * Promise.resolve.
 */
 function useValidators(validators) {
    const initialState = {}

    for (const field of Object.keys(validators)) {
        initialState[field] = { value: '', dirty: false, error: null }
    }

    const [fields, setFields] = useState(initialState)

    return {
        get: (field) => fields[field].value,

        isInvalid: (field) => fields[field].dirty && fields[field].error !== null,
        
        getError: (field) => fields[field].error,

        set: (field) => debounce(
            500,
    
            // Go through each validation function and stop the promise chain
            // when an error is not null.
            //
            // Note: a === accumilated promise
            //       c === current promise
            //       e === error message
            ({ target }) => validators[field]
                .reduce((a, c) => a.then(e => e ?? c(target.value)), Promise.resolve(null))
                .then((error) => {
                    setFields((prev) => ({
                        ...prev,
                        [field]: { value: target.value, dirty: true, error }
                    }))
    
                    return error === null
                })
                .catch((error) => {
                    setFields((prev) => ({
                        ...prev,
                        [field]: { value: target.value, dirty: true, error: 'Invalid value.' }
                    }))
    
                    return false
                }),
    
            ({ key, target }) => key === 'Enter' || (key === 'Backspace' && target.value === '')
        ),
        
        // This eyesore goes through all of the validators for each field
        // and returns the overall state of the set of fields.
        getValidity: () => Promise.all(Object.keys(fields).map((field) => (
            validators[field]
                .reduce((a, fn) => a.then((e) => e ?? fn(fields[field].value)), Promise.resolve(null))
                .then((error) => {
                    setFields((prev) => ({
                        ...prev,
                        [field]: { value: fields[field].value, dirty: true, error }
                    }))
    
                    return error === null
                })
                .catch((error) => {
                    setFields((prev) => ({
                        ...prev,
                        [field]: { value: fields[field].value, dirty: true, error: 'Invalid value.' }
                    }))
    
                    return false
                })
        ))).then((res) => res.reduce((allValid, valid) => allValid && valid, true))
    }
}

export default useValidators