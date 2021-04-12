// A decorator function that runs the callback only when a certain
// amount of time has passed since the last time the function was invoked.
//
// Inspired by the following implementation by David Walsh:
// https://davidwalsh.name/javascript-debounce-function
function debounce(time, callback, override = undefined) {
    let active = false
    let timeout = null

    return function (...args) {
        // Run the following after the timeout is complete.
        //
        // A nice quirk of arrow functions is that they have no "this",
        // so one can reference the parent context without setting it to
        // another variable. Before arrow functions were introduced,
        // it was common to do "var self = this" to reference the parent
        // function's context.
        const handler = () => {
            callback.apply(this, args)
            active = false
        }

        active && window.clearTimeout(timeout)

        // If override condition is set, ignore debouncing behavior.
        if (typeof override === 'function' && override.apply(this, args)) {
            handler()
        } else {
            timeout = window.setTimeout(handler, time)
            active = true
        }
    }
}

export default debounce
