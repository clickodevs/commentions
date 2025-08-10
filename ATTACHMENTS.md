# Commentions Attachment Feature

The Commentions plugin now supports file attachments with comments. Users can attach up to 5 files per comment, with each file having a maximum size of 20MB.

## Features

- **File Upload**: Support for any file type
- **Multiple Files**: Up to 5 files per comment
- **Size Limit**: 20MB per file
- **File Preview**: Different icons for different file types (images, videos, audio, documents)
- **Download**: Direct download links for all attachments
- **File Information**: Display file name and size

## Installation

### For New Installations

The attachment feature is included by default. Just run the standard migration:

```bash
php artisan vendor:publish --tag="commentions-migrations"
php artisan migrate
```

### For Existing Installations

If you already have Commentions installed, you need to add the attachments column:

```bash
php artisan vendor:publish --tag="commentions-migrations"
php artisan migrate
```

This will add the new `attachments` JSON column to your existing comments table.

## Usage

### Backend

The `Comment` model now includes several attachment-related methods:

```php
// Check if comment has attachments
$comment->hasAttachments();

// Get attachment count
$comment->getAttachmentCount();

// Get all attachments
$comment->getAttachments();

// Get formatted file size
$comment->getFormattedFileSize($filePath);
```

### Frontend

The attachment UI is automatically included in the comment form. Users can:

1. Click "Attach Files" to select files
2. Preview selected files with remove option
3. Submit comment with attachments
4. View and download attachments in posted comments

## File Storage

Files are stored in the `storage/app/public/commentions/attachments/` directory and are accessible via the public disk.

## Security Considerations

- File validation is performed on upload
- Files are stored outside the web root by default
- Download links use Laravel's storage URL generation
- Consider implementing additional virus scanning for production use

## Customization

You can customize the attachment limits by modifying the validation rules in the Comments Livewire component:

```php
protected $rules = [
    'commentBody' => 'required|string',
    'attachments.*' => 'file|max:20480', // Change size limit here
];
```

To change the maximum number of files, modify the validation in the `updatedAttachments()` method.
