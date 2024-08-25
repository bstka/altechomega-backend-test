<?php

namespace App\Serializer\Normalizer;

use App\Entity\Book;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BookNormalizer implements NormalizerInterface
{
  public function __construct(
    #[Autowire(service: 'serializer.normalizer.object')]
    private readonly NormalizerInterface $normalizer,
  ) {}

  public function normalize($book, ?string $format = null, array $context = []): array
  {
    // $data = $this->normalizer->normalize($author, $format, $context);
    
    $data = [
      "id" => $book->getId(),
      "title" => $book->getTitle(),
      "description" => $book->getDescription(),
      "publish_date" => $book->getPublishDate()->format('Y-m-d'),
      "author_id" => $book->getAuthorId(),
    ];

    return $data;
  }

  public function supportsNormalization($data, ?string $format = null, array $context = []): bool
  {
    return $data instanceof Book;
  }

  public function getSupportedTypes(?string $format): array
  {
    return [
      Book::class => true,
    ];
  }
}
