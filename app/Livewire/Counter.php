<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Counter extends Component
{
    public int $counter = 0;
    public bool $isLoading = false;

    protected $listeners = ['resetCounter' => 'resetCounter'];

    public function mount(): void
    {
        $this->loadCounterFromSession();
    }

    /**
     * Load counter value from session.
     */
    public function loadCounterFromSession(): void
    {
        $this->counter = session()->get('counter', 0);
    }

    /**
     * Increment the counter.
     */
    public function increment(): void
    {
        $this->isLoading = true;

        try {
            $this->counter++;
            $this->saveCounterToSession();
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Decrement the counter.
     */
    public function decrement(): void
    {
        $this->isLoading = true;

        try {
            $this->counter--;
            $this->saveCounterToSession();
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Reset the counter to 0.
     */
    public function resetCounter(): void
    {
        $this->isLoading = true;

        try {
            $this->counter = 0;
            $this->saveCounterToSession();
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Save counter value to session.
     */
    private function saveCounterToSession(): void
    {
        session()->put('counter', $this->counter);
    }

    /**
     * Get the current counter value (for external access).
     */
    public function getCounterProperty(): int
    {
        return $this->counter;
    }

    /**
     * Check if counter is in valid state.
     */
    public function isValid(): bool
    {
        return is_int($this->counter);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.counter');
    }
}
