import { useState } from 'react'
import { Switch, Route, Link, Redirect, useRouteMatch, useHistory } from 'react-router-dom'

import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Nav from 'react-bootstrap/Nav'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import BackButton from '../components/BackButton'

function SettingsPage() {
    const { url, path } = useRouteMatch()
    const history = useHistory()
    const pages = ['account', 'pets', 'subscriptions']
    const [page, setPage] = useState('account')
    const goToPage = p => () => {
        setPage(p)
        history.replace(`${url}/${p}`)
    }

    return (
        <>
            <h1><BackButton />Settings</h1>

            <ButtonGroup className="d-flex my-4">
                {pages.map((p, i) => (
                    <Button
                        key={i}
                        variant={p === page ? 'primary' : 'secondary'}
                        onClick={goToPage(p)}>{`${p.charAt(0).toUpperCase()}${p.substr(1)}`}</Button>
                ))}
            </ButtonGroup>

            <Switch>
                <Route path={`${path}/account`}>
                    <p>My account</p>
                </Route>

                <Route path={`${path}/pets`}>
                    <p>My Pets</p>
                </Route>

                <Route path={`${path}/subscriptions`}>
                    <p>Manage Subscriptions</p>
                </Route>

                <Route exact path={`${path}`}>
                    <Redirect to={`${url}/account`} />
                </Route>
            </Switch>
        </>
    )
}

export default SettingsPage