<?php

namespace Laravel\Ai\Responses\Data;

readonly class TextUsage extends Usage
{
    /**
     * @param  int  $inputTokens  Total input tokens, including any cached or cache-written tokens.
     * @param  int  $outputTokens  Total output tokens, including any reasoning tokens.
     * @param  int|null  $cacheReadInputTokens  Subset of the input tokens read from a prompt cache, or null when unreported.
     * @param  int|null  $cacheWriteInputTokens  Subset of the input tokens written to a prompt cache, or null when unreported.
     * @param  int|null  $reasoningTokens  Subset of the output tokens spent on reasoning, or null when unreported.
     * @param  float|null  $cost  Cost of the request as reported by the provider, or null when unreported.
     */
    public function __construct(
        int $inputTokens = 0,
        int $outputTokens = 0,
        public ?int $cacheReadInputTokens = null,
        public ?int $cacheWriteInputTokens = null,
        public ?int $reasoningTokens = null,
        public ?float $cost = null,
    ) {
        parent::__construct($inputTokens, $outputTokens);
    }

    /**
     * Reconstruct an instance from a previously serialized toArray() payload.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            inputTokens: $data['input_tokens'] ?? 0,
            outputTokens: $data['output_tokens'] ?? 0,
            cacheReadInputTokens: $data['cache_read_input_tokens'] ?? null,
            cacheWriteInputTokens: $data['cache_write_input_tokens'] ?? null,
            reasoningTokens: $data['reasoning_tokens'] ?? null,
            cost: isset($data['cost']) ? (float) $data['cost'] : null,
        );
    }

    /**
     * Get the input tokens that were neither read from nor written to a prompt cache.
     */
    public function uncachedInputTokens(): int
    {
        return $this->inputTokens - ($this->cacheReadInputTokens ?? 0) - ($this->cacheWriteInputTokens ?? 0);
    }

    /**
     * Add the given usage to the current usage and return a new usage instance.
     */
    public function add(TextUsage $usage): TextUsage
    {
        return new TextUsage(
            $this->inputTokens + $usage->inputTokens,
            $this->outputTokens + $usage->outputTokens,
            static::sum($this->cacheReadInputTokens, $usage->cacheReadInputTokens),
            static::sum($this->cacheWriteInputTokens, $usage->cacheWriteInputTokens),
            static::sum($this->reasoningTokens, $usage->reasoningTokens),
            static::sum($this->cost, $usage->cost),
        );
    }

    /**
     * Sum two optional counts, preserving null when neither was reported.
     *
     * @template T of int|float
     *
     * @param  T|null  $a
     * @param  T|null  $b
     * @return T|null
     */
    protected static function sum(int|float|null $a, int|float|null $b): int|float|null
    {
        return $a === null && $b === null ? null : ($a ?? 0) + ($b ?? 0);
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'cache_read_input_tokens' => $this->cacheReadInputTokens,
            'cache_write_input_tokens' => $this->cacheWriteInputTokens,
            'reasoning_tokens' => $this->reasoningTokens,
            'cost' => $this->cost,
        ];
    }
}
