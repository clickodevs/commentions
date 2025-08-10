<?php

namespace Kirschbaum\Commentions\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Events\CommentWasCreatedEvent;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;

class SaveComment
{
  /**
   * @throws AuthorizationException
   */
  public function __invoke(Model $commentable, Commenter $author, string $body, array $attachments = []): Comment
  {
    if ($author->cannot('create', Config::getCommentModel())) {
      throw new AuthorizationException('Cannot create comment');
    }

    $comment = $commentable->comments()->create([
      'body' => $body,
      'author_id' => $author->getKey(),
      'author_type' => $author->getMorphClass(),
      'attachments' => $attachments,
    ]);

    $this->dispatchEvents($comment);

    return $comment;
  }

  protected function dispatchEvents(Comment $comment): void
  {
    if ($comment->wasRecentlyCreated) {
      CommentWasCreatedEvent::dispatch($comment);
    }

    $mentionees = $comment->getMentioned();

    $mentionees->each(function ($mentionee) use ($comment) {
      UserWasMentionedEvent::dispatch($comment, $mentionee);
    });
  }

  public static function run(...$args)
  {
    return (new static())(...$args);
  }
}
