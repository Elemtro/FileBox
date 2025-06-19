<?php
// src/ArgumentResolver/DtoResolver.php
namespace App\Api\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class DtoResolver implements ValueResolverInterface
{
    public function __construct(private SerializerInterface $serializer) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return str_starts_with($argument->getType() ?? '', 'App\\DTO\\');
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->serializer->deserialize(
            $request->getContent(),
            $argument->getType(),
            'json'
        );
    }
}
