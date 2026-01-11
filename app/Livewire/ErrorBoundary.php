<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ErrorBoundary extends Component
{
    public string $errorMessage = '';
    public string $errorCode = '';
    public bool $showDetails = false;

    public function mount($error = null, $code = null)
    {
        if ($error) {
            $this->errorMessage = $error;
            $this->errorCode = $code ?? '500';
            $this->logError($error, $code);
        }
    }

    public function retry()
    {
        $this->reset(['errorMessage', 'errorCode', 'showDetails']);
        $this->dispatch('error-resolved');
    }

    public function toggleDetails()
    {
        $this->showDetails = !$this->showDetails;
    }

    protected function logError($message, $code)
    {
        Log::error('Livewire Error Boundary', [
            'error' => $message,
            'code' => $code,
            'url' => request()->url(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
    }

    public function render()
    {
        return view('livewire.error-boundary');
    }
}
