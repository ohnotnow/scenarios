# Simple PHP Scenarios

This is a *very* basic PHP class and trait to make using 'scenarios' easier.  Initially it
was written with Laravel phpunit tests in mind, but in theory it could be used for other things.

## What are... 'Scenarios'?

When writing tests I often find myself having to do repetative things like this a lot in each test :

```php
$admin = factory(User::class)->states('admin')->create();
$user = factory(User::class)->states('regular')->create();
$post = factory(Post::class, 3)->states('unpublished')->create(['user_id' => $user->id]);
...
```

That code gets repeated in maybe a dozen tests.  So sometimes I write a helper function - sometimes
not as it's quicker to just copy'n'paste the code from the previous test.  So I decided
to write a very simple helper which is pretty much a key/value store so that I could do something
a little more expressive in the test like the following :

```php
$this->scenarios()->playout('there is an admin and a user with three posts');
```

## Usage

Assuming you want to use the trait :

```php
class SomeTest {

    use \Ohffs\Scenarios\HasScenarios;

    public function setUp()
    {
        parent::setUp();
        $this->scenarios()->write('there is an unpublished post and an admin', function ($params) {
            $admin = factory(User::class)->states('admin')->create();
            $post = factory(Post::class)->states('unpublished')->create($params);
            return [$admin, $post];
        });
        $this->scenarios()->write('we have a published post', function () {
            return factory(Post::class)->states('published')->create();
        });
        $this->scenarios()->write('we have an admin', function () {
            return factory(User::class)->states('admin')->create();
        });
        $this->scenarios()->write('we have a regular user', function () {
            return factory(User::class)->states('regular')->create();
        });
    }

    public function test_an_admin_can_mark_a_post_as_published()
    {
        [$admin, $post] = $this->scenarios()
                            ->playout('there is an unpublished post and an admin', ['title' => 'A Post Title'])
                            ->andReturnResults();

        $response = $this->actingAs($admin)->post('/posts/' . $post->id, ['status' => 'published']);

        $this->assertEquals(1, Post::published()->count());
    }

    public function test_an_admin_can_delete_a_post()
    {
        [$admin, $post] = $this->scenarios()->playout('there is an unpublished post and an admin')->andReturnResults();

        $response = $this->actingAs($admin)->delete('/posts/' . $post->id);

        $this->assertEquals(0, Post::count());
    }


    public function test_a_user_cant_delete_posts_that_are_not_theirs()
    {
        [$post, $badUser] = $this->scenarios()
                                ->playout('we have a published post')
                                ->andAlso('we have a regular user')
                                ->andReturnResults();

        $response = $this->actingAs($badUser)->delete('/posts/' . $post->id);

        $response->assertStatus(302);
        $this->assertEquals(1, Post::count());
    }
}
```

## Notes

If you try and 'playout' a scenario which doesn't exist, the code will throw an \InvalidArgumentException.

If your scenario list only calls one 'thing' then it will be returned 'as is', otherwise it'll return an array of
the things. Eg.

```php
$this->scenarios()->write('we have a regular user', function () {
    return ['username' => 'jenny', 'status' => 'normal'];
});
$this->scenarios()->write('we have a super user', function () {
    return ['username' => 'marlene', 'status' => 'admin'];
});

$admin = $this->scenarios()->playout('we have a super user')->andReturnResults();
$user = $this->scenarios()->playout('we have a regular user')->andReturnResults();
[$user, $admin] = $this->scenarios()->playout('we have a regular user')->andAlso('we have a super user')->andReturnResults();
```
