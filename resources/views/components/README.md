# Laravel Blade Components Documentation

This directory contains reusable Blade components for the application. Below is a guide for using each component.

## Layout Components

### App Layout
```blade
<x-app-layout>
    <x-slot name="header">
        <!-- Header content -->
    </x-slot>
    
    <!-- Main content -->
    
    @push('scripts')
        <!-- Additional scripts -->
    @endpush
</x-app-layout>
```

### Guest Layout
```blade
<x-guest-layout>
    <!-- Content for unauthenticated users -->
</x-guest-layout>
```

## Form Components

### Input Label
```blade
<x-input-label for="field_name" :value="__('Label Text')" />
```

### Text Input
```blade
<x-text-input id="field_name" name="field_name" type="text" :value="old('field_name')" required />
```

### Input Error
```blade
<x-input-error :messages="$errors->get('field_name')" class="mt-2" />
```

### Primary Button
```blade
<x-primary-button>
    {{ __('Button Text') }}
</x-primary-button>
```

## Navigation Components

### Nav Link
```blade
<x-nav-link :href="route('route.name')" :active="request()->routeIs('route.name')">
    {{ __('Link Text') }}
</x-nav-link>
```

### Responsive Nav Link
```blade
<x-responsive-nav-link :href="route('route.name')" :active="request()->routeIs('route.name')">
    {{ __('Link Text') }}
</x-responsive-nav-link>
```

## UI Components

### Dropdown
```blade
<x-dropdown align="right" width="48">
    <x-slot name="trigger">
        <!-- Trigger content -->
    </x-slot>

    <x-slot name="content">
        <!-- Dropdown content -->
    </x-slot>
</x-dropdown>
```

### Dropdown Link
```blade
<x-dropdown-link :href="route('route.name')">
    {{ __('Link Text') }}
</x-dropdown-link>
```

### Badge
```blade
<x-badge type="success">
    {{ __('Badge Text') }}
</x-badge>
```

### Button
```blade
<x-button type="primary">
    {{ __('Button Text') }}
</x-button>
```

### Card
```blade
<x-card>
    <!-- Card content -->
</x-card>
```

### Notification
```blade
<x-notification type="success">
    {{ __('Notification Message') }}
</x-notification>
```

### Flash Message
```blade
<x-flash-message :message="session('success')" />
```

## Application Logo
```blade
<x-application-logo class="h-8 w-8" />
```

## Best Practices

1. Always use components for repeated UI elements
2. Keep components focused and single-purpose
3. Use slots for flexible content
4. Maintain consistent styling across components
5. Document any special attributes or requirements 
