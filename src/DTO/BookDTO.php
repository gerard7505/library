<?php
namespace App\DTO;

class BookDTO
{
    public ?string $isbn;
    public ?string $title;
    public ?string $author;
    public ?string $publisher;
    public ?\DateTime $published;
    public ?string $subtitle;
    public ?string $description;
    public ?string $website;
    public ?int $pages;
    public ?string $category;
    public array $images;

    public function __construct(
        string $isbn,
        string $title,
        ?string $author,
        ?string $publisher,
        ?\DateTime $published,
        ?string $subtitle,
        ?string $description,
        ?string $website,
        ?int $pages,
        ?string $category,
        array $images
    ) {
        $this->isbn = $isbn;
        $this->title = $title;
        $this->author = $author;
        $this->publisher = $publisher;
        $this->published = $published;
        $this->subtitle = $subtitle;
        $this->description = $description;
        $this->website = $website;
        $this->pages = $pages;
        $this->category = $category;
        $this->images = $images;
    }
}
