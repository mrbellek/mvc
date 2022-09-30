<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;

class Blog extends Controller
{
    public function index(): void
    {
        $posts = $this->model->getAll();
        if (count($posts) === 0) {
            $this->setInfo('No blog posts found.');
        }
        $this->set('posts', $posts);
    }

    public function edit($id): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->editPost($id);
            return;
        }

        $this->set('title', 'Edit blog post');
        $post = $this->model->get(intval($id));
        if ($post !== null) {
            $this->set('post', $post);
        } else {
            $this->setError(sprintf('Blog post with id %s was not found.', $id));
        }
    }

    public function add(): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->addPost();
            return;
        }

        $this->set('title', 'Add blog post');
    }

    private function editPost($id): void
    {
        $title = filter_input(INPUT_POST, 'title');
        $body = filter_input(INPUT_POST, 'body');

        if ($this->model->edit(intval($id), $title, $body) === true) {
            $this->setDelayedInfo('Post edited.');
            $this->redirect('/blog');
        } else {
            $this->setDelayedError('Failed to edit post.');
            $this->redirect(sprintf('/blog/edit/%d', $id));
        }
    }

    private function addPost(): void
    {
        $title = filter_input(INPUT_POST, 'title');
        $body = filter_input(INPUT_POST, 'body');

        if ($this->model->add($title, $body) === true) {
            $this->setDelayedInfo('Post added.');
        } else {
            $this->setDelayedError('Failed to add post.');
        }
        $this->redirect('/blog');
    }

    public function delete($id)
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $ret = $this->model->delete(intval($id));
            if ($ret) {
                $this->setDelayedInfo('Blog post deleted.');
            } else {
                $this->setDelayedError('Failed to delete post.');
            }
            $this->redirect('/blog');
        }

        $post = $this->model->get(intval($id));
        if ($post) {
            $this->set('title', $post['title']);
        } else {
            $this->setDelayedError('Blog post not found.');
            $this->redirect('/blog');
        }
    }
}