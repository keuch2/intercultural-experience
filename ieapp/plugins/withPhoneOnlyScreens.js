/**
 * Distribución solo para smartphones: declara que la app no soporta pantallas grandes
 * para que Google Play la filtre en tablets (complementa la exclusión en el catálogo de dispositivos).
 * También marca como opcionales las funciones de hardware que Play deduce (orientación, wifi).
 */
const { withAndroidManifest } = require('@expo/config-plugins');

module.exports = function withPhoneOnlyScreens(config) {
  return withAndroidManifest(config, (cfg) => {
    cfg.modResults.manifest['supports-screens'] = [
      {
        $: {
          'android:smallScreens': 'true',
          'android:normalScreens': 'true',
          'android:largeScreens': 'false',
          'android:xlargeScreens': 'false',
          'android:anyDensity': 'true',
        },
      },
    ];
    // Play deduce funciones obligatorias de la orientación fija (screen.portrait) y del permiso
    // ACCESS_WIFI_STATE de netinfo (wifi); se declaran opcionales para no restringir dispositivos.
    const optional = ['android.hardware.screen.portrait', 'android.hardware.wifi'];
    const features = (cfg.modResults.manifest['uses-feature'] ?? []).filter(
      (f) => !optional.includes(f.$?.['android:name']),
    );
    cfg.modResults.manifest['uses-feature'] = [
      ...features,
      ...optional.map((name) => ({ $: { 'android:name': name, 'android:required': 'false' } })),
    ];
    return cfg;
  });
};
