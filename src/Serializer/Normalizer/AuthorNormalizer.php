<?php

namespace App\Serializer\Normalizer;

use App\Entity\Author;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AuthorNormalizer implements NormalizerInterface
{
  public function __construct(
    #[Autowire(service: 'serializer.normalizer.object')]
    private readonly NormalizerInterface $normalizer,
  ) {}

  public function normalize($author, ?string $format = null, array $context = []): array
  {
    // $data = $this->normalizer->normalize($author, $format, $context);
    $bookIsWanted = false;

    if (array_key_exists('groups', $context)) {
      $bookIsWanted = in_array('book:read', $context['groups']);
    }

    $data = [
      'id' => $author->getId(),
      'name' => $author->getName(),
      'bio' => $author->getBio(),
      'birth_date' => $author->getBirthDate()->format('Y-m-d')
    ];

    if ($bookIsWanted) {
      $data['books'] = $author->getBooks();
    }

    return $data;
  }

  public function supportsNormalization($data, ?string $format = null, array $context = []): bool
  {
    return $data instanceof Author;
  }

  public function getSupportedTypes(?string $format): array
  {
    return [
      Author::class => true,
    ];
  }
}
