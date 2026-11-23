// google-services.json no está en el repo: en EAS llega como variable de tipo archivo
// (GOOGLE_SERVICES_JSON); en local se usa la copia ignorada en ieapp/.
module.exports = ({ config }) => ({
  ...config,
  android: {
    ...config.android,
    googleServicesFile: process.env.GOOGLE_SERVICES_JSON ?? './google-services.json',
  },
});
