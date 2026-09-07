import React from 'react';
import { useRoute, RouteProp } from '@react-navigation/native';
import DocumentUploadForm, { PickedFile } from '../../components/documents/DocumentUploadForm';
import { auPairService } from '../../services/api';
import { AuPairDocumentEntry } from '../../types/aupair';

type RouteP = RouteProp<{ AuPairDocumentUpload: { entry: AuPairDocumentEntry } }, 'AuPairDocumentUpload'>;

/**
 * Subida de documentos Au Pair. El formulario es el compartido con el motor
 * genérico (components/documents/DocumentUploadForm); acá solo se define el endpoint.
 */
const AuPairDocumentUploadScreen: React.FC = () => {
  const entry = useRoute<RouteP>().params.entry;

  const onUpload = async (files: PickedFile[]) => {
    await auPairService.uploadDocument({ document_type: entry.document_type, stage: entry.stage, files });
  };

  return (
    <DocumentUploadForm
      entry={entry}
      onUpload={onUpload}
      hint={`Formatos: JPG, PNG, PDF${entry.document_type === 'presentation_video' ? ', MP4 (hasta 200MB)' : ''}.`}
    />
  );
};

export default AuPairDocumentUploadScreen;
