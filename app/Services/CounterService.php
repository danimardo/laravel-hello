<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CounterService
{
    private const COUNTER_SESSION_KEY = 'counter';

    /**
     * Get the current counter value from session.
     */
    public function getCurrentValue(Request $request): int
    {
        return $request->session()->get(self::COUNTER_SESSION_KEY, 0);
    }

    /**
     * Increment the counter by 1.
     */
    public function increment(Request $request): int
    {
        $currentValue = $this->getCurrentValue($request);
        $newValue = $currentValue + 1;

        $this->saveValue($request, $newValue);

        return $newValue;
    }

    /**
     * Decrement the counter by 1.
     */
    public function decrement(Request $request): int
    {
        $currentValue = $this->getCurrentValue($request);
        $newValue = $currentValue - 1;

        $this->saveValue($request, $newValue);

        return $newValue;
    }

    /**
     * Reset the counter to 0.
     */
    public function reset(Request $request): int
    {
        $this->saveValue($request, 0);

        return 0;
    }

    /**
     * Set a specific value to the counter.
     */
    public function setValue(Request $request, int $value): int
    {
        // Validate the value
        if (!$this->isValidValue($value)) {
            throw new \InvalidArgumentException('Valor de contador inválido');
        }

        $this->saveValue($request, $value);

        return $value;
    }

    /**
     * Save counter value to session.
     */
    private function saveValue(Request $request, int $value): void
    {
        $request->session()->put(self::COUNTER_SESSION_KEY, $value);
    }

    /**
     * Check if counter is in a valid state.
     */
    public function isValid(Request $request): bool
    {
        $value = $this->getCurrentValue($request);

        return $this->isValidValue($value);
    }

    /**
     * Validate counter value.
     */
    private function isValidValue(mixed $value): bool
    {
        return is_int($value) && $value >= PHP_INT_MIN && $value <= PHP_INT_MAX;
    }

    /**
     * Recover counter from invalid state.
     */
    public function recover(Request $request): int
    {
        $currentValue = $this->getCurrentValue($request);

        // If value is invalid, reset to 0
        if (!$this->isValidValue($currentValue)) {
            $this->saveValue($request, 0);
            return 0;
        }

        return $currentValue;
    }

    /**
     * Get counter history (if needed for future features).
     */
    public function getHistory(Request $request): array
    {
        return $request->session()->get('counter_history', []);
    }

    /**
     * Add to counter history.
     */
    private function addToHistory(Request $request, int $oldValue, int $newValue, string $action): void
    {
        $history = $this->getHistory($request);

        $history[] = [
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'action' => $action,
            'timestamp' => now(),
        ];

        // Keep only last 10 actions
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        $request->session()->put('counter_history', $history);
    }

    /**
     * Clear counter history.
     */
    public function clearHistory(Request $request): void
    {
        $request->session()->forget('counter_history');
    }

    /**
     * Get counter statistics.
     */
    public function getStats(Request $request): array
    {
        $currentValue = $this->getCurrentValue($request);
        $history = $this->getHistory($request);

        return [
            'current_value' => $currentValue,
            'total_operations' => count($history),
            'last_action' => !empty($history) ? $history[count($history) - 1] : null,
        ];
    }
}
