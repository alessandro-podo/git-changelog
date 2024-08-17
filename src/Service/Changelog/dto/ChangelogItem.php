<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;

final readonly class ChangelogItem
{
    public function __construct(
        public string $id,
        public string $visibilityCode,
        public string $title,
        public ?string $description,
        public ?string $scope,
        public CommitType $type,
        public bool $bcBreak,
    ) {}

    /**
     * @return array{id:string,visibilityCode:string,title:string,description:null|string,scope:null|string,type:string,bc:bool}
     *
     * @codeCoverageIgnore
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'visibilityCode' => $this->visibilityCode,
            'title' => $this->title,
            'description' => $this->description,
            'scope' => $this->scope,
            'type' => $this->type->value,
            'bc' => $this->bcBreak,
        ];
    }
}
