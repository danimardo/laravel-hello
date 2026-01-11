@props(['value' => ''])

<div class="password-strength-indicator" x-data="passwordStrength('{{ $value }}')">
    <div class="form-control w-full">
        <div class="flex justify-between items-center mb-1">
            <label class="label-text font-semibold">Fortaleza de la contraseña</label>
            <span class="text-xs font-semibold" :class="strengthColor" x-text="strengthText"></span>
        </div>
        <div class="w-full bg-base-300 rounded-full h-2.5">
            <div class="h-2.5 rounded-full transition-all duration-300" :class="progressBarColor" :style="'width: ' + strengthPercentage + '%'"></div>
        </div>
        <div class="mt-2 text-xs space-y-1">
            <div class="flex items-center gap-2" :class="{ 'text-success': hasMinLength }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{ 'text-success': hasMinLength, 'text-base-content/50': !hasMinLength }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span :class="{ 'text-success': hasMinLength }">Al menos 8 caracteres</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-success': hasLowercase }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{ 'text-success': hasLowercase, 'text-base-content/50': !hasLowercase }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span :class="{ 'text-success': hasLowercase }">Al menos una letra minúscula</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-success': hasUppercase }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{ 'text-success': hasUppercase, 'text-base-content/50': !hasUppercase }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span :class="{ 'text-success': hasUppercase }">Al menos una letra mayúscula</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-success': hasNumber }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{ 'text-success': hasNumber, 'text-base-content/50': !hasNumber }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span :class="{ 'text-success': hasNumber }">Al menos un número</span>
            </div>
            <div class="flex items-center gap-2" :class="{ 'text-success': hasSpecial }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{ 'text-success': hasSpecial, 'text-base-content/50': !hasSpecial }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span :class="{ 'text-success': hasSpecial }">Al menos un carácter especial (!@#$%^&*)</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('passwordStrength', (initialValue = '') => ({
            value: initialValue,

            get hasMinLength() {
                return this.value.length >= 8;
            },

            get hasLowercase() {
                return /[a-z]/.test(this.value);
            },

            get hasUppercase() {
                return /[A-Z]/.test(this.value);
            },

            get hasNumber() {
                return /\d/.test(this.value);
            },

            get hasSpecial() {
                return /[!@#$%^&*()\-_=+{};:,<.>/?~]/.test(this.value);
            },

            get criteriaCount() {
                let count = 0;
                if (this.hasMinLength) count++;
                if (this.hasLowercase) count++;
                if (this.hasUppercase) count++;
                if (this.hasNumber) count++;
                if (this.hasSpecial) count++;
                return count;
            },

            get strengthPercentage() {
                return (this.criteriaCount / 5) * 100;
            },

            get strengthText() {
                if (this.criteriaCount === 0) return '';
                if (this.criteriaCount < 3) return 'Débil';
                if (this.criteriaCount < 5) return 'Media';
                return 'Fuerte';
            },

            get strengthColor() {
                if (this.criteriaCount === 0) return '';
                if (this.criteriaCount < 3) return 'text-error';
                if (this.criteriaCount < 5) return 'text-warning';
                return 'text-success';
            },

            get progressBarColor() {
                if (this.criteriaCount === 0) return 'bg-base-300';
                if (this.criteriaCount < 3) return 'bg-error';
                if (this.criteriaCount < 5) return 'bg-warning';
                return 'bg-success';
            }
        }));
    });
</script>
