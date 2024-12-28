import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/guava/calendar/resources/**/*.blade.php',
        './vendor/jaocero/activity-timeline/resources/views/**/*.blade.php',
    ],
}
