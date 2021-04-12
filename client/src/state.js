import { BehaviorSubject } from 'rxjs/internals/BehaviorSubject'

// Currently logged in user.
// Should also contain the user's pets, if they have any.
// By convention, observables are suffixed with a dollar symbol.
export const session$ = new BehaviorSubject(null)