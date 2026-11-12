/**
 * Distribución solo para smartphones: declara que la app no soporta pantallas grandes
 * para que Google Play la filtre en tablets (complementa la exclusión en el catálogo de dispositivos).
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
    return cfg;
  });
};
