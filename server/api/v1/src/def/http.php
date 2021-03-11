<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Http;
use ThePetPark\Repositories\PetRepository;
use ThePetPark\Repositories\PostRepository;
use ThePetPark\Repositories\UserRepository;

use function DI\factory;

/**
 * Controllers unrelated to API resources are bootstrapped here.
 */
return [

    Http\HelloWorld::class => factory(function (ContainerInterface $c) {
        return new Http\HelloWorld();
    }),

    Http\Search::class => factory(function (ContainerInterface $c) {
        return new Http\Search(
            $c->get(UserRepository::class),
            $c->get(PetRepository::class),
            $c->get(PostRepository::class)
        );
    }),

    Http\UploadFile::class => factory(function (ContainerInterface $c) {
        return new Http\UploadFile();
    }),

    Http\Auth\EchoSession::class => factory(function (ContainerInterface $c) {
        return new Http\Auth\EchoSession();
    }),

    Http\Auth\Login::class => factory(function (ContainerInterface $c) {
        return new Http\Auth\Login(
            $c->get(UserRepository::class),
            $c->get('jwt_encoder')
        );
    }),

    Http\Auth\Logout::class => factory(function (ContainerInterface $c) {
        return new Http\Auth\Logout();
    }),

    Http\Auth\Register::class => factory(function (ContainerInterface $c) {
        return new Http\Auth\Register($c->get(UserRepository::class));
    }),

];