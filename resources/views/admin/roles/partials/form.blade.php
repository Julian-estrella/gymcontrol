@php
    $selectedModules = old('modules', $role?->modules ?? []);
    $canAccessAdmin = old('can_access_admin', $role?->can_access_admin ?? false);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
        <input type="text" name="name" id="name" value="{{ old('name', $role?->name) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
        @error('name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Identificador</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $role?->slug) }}" {{ $role?->is_system ? 'readonly' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('slug') border-red-500 @enderror {{ $role?->is_system ? 'bg-gray-100' : '' }}">
        @error('slug')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="col-span-1 md:col-span-2">
        <div class="flex items-center">
            <input type="checkbox" name="can_access_admin" id="can_access_admin" value="1" {{ $canAccessAdmin ? 'checked' : '' }}
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="can_access_admin" class="ml-2 block text-sm text-gray-900">
                Puede entrar al dashboard de administracion
            </label>
        </div>
        @error('can_access_admin')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="col-span-1 md:col-span-2 border-t border-gray-200 mt-2 pt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Modulos permitidos</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($modules as $key => $module)
                <label class="flex items-center p-4 border border-gray-200 rounded-md hover:bg-gray-50">
                    <input type="checkbox" name="modules[]" value="{{ $key }}" {{ in_array($key, $selectedModules, true) ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-800">
                        <i class="fa-solid {{ $module['icon'] }} w-5 text-center mr-2 text-gray-500"></i>
                        {{ $module['label'] }}
                    </span>
                </label>
            @endforeach
        </div>

        @error('modules')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex justify-end">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow">
        <i class="fa-solid fa-save mr-2"></i> Guardar Rol
    </button>
</div>
