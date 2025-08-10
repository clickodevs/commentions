<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

class Comments extends Component
{
    use HasMentions;
    use HasPagination;
    use HasPolling;
    use WithFileUploads;

    protected $listeners = ['refreshComments' => '$refresh'];

    public Model $record;

    public string $commentBody = '';

    public array $attachments = [];

    protected $rules = [
        'commentBody' => 'required|string',
        'attachments.*' => 'file|max:20480', // 20MB max per file
    ];

    public function updatedAttachments()
    {
        $this->validate([
            'attachments.*' => 'file|max:20480',
        ]);

        // Check maximum number of files
        if (count($this->attachments) > 5) {
            $this->addError('attachments', 'You can upload a maximum of 5 files.');
            $this->attachments = array_slice($this->attachments, 0, 5);
        }
    }

    #[Renderless]
    public function save()
    {
        $this->validate();

        $savedAttachments = [];

        // Process file uploads
        foreach ($this->attachments as $attachment) {
            if ($attachment) {
                $path = $attachment->store('commentions/attachments', 'public');
                $savedAttachments[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'size' => $attachment->getSize(),
                    'mime_type' => $attachment->getMimeType(),
                ];
            }
        }

        SaveComment::run(
            $this->record,
            Config::resolveAuthenticatedUser(),
            $this->commentBody,
            $savedAttachments
        );

        $this->clear();
        $this->dispatch('comment:saved');
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function render()
    {
        return view('commentions::comments');
    }

    #[On('body:updated')]
    #[Renderless]
    public function updateCommentBodyContent($value): void
    {
        $this->commentBody = $value;
    }

    #[Renderless]
    public function clear(): void
    {
        $this->commentBody = '';
        $this->attachments = [];

        $this->dispatch('comments:content:cleared');
    }
}
