<?php


namespace App\Operations;


use App\Jobs\BaseSSHJob;
use App\Jobs\Operations\FinishOperationJob;
use App\Jobs\Operations\StartOperationJob;
use App\Models\Node;

abstract class Operation
{
    public const QUEUE = 'operations';

    /**
     * @var array
     */
    protected array $chain = [];

    /**
     * @var Node
     */
    protected Node $node;

    /**
     * BaseOperation constructor.
     * @param Node $node
     *
     * @return void
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    /**
     * @param BaseSSHJob $job
     *
     * @return void
     */
    public function addJob(BaseSSHJob $job)
    {
        $this->chain[] = $job;
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        $this->build();

        $this->addJob(new FinishOperationJob($this->node->id));

        StartOperationJob::withChain($this->chain)
            ->dispatch($this->node->id, $this->name())
            ->allOnQueue(static::QUEUE);
    }

    /**
     * @return string
     */
    abstract protected function name(): string;

    /**
     * @return void
     */
    abstract protected function build(): void;
}
