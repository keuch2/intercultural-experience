import React from 'react';
import { useRoute, RouteProp } from '@react-navigation/native';
import DocumentUploadForm, { PickedFile } from '../../components/documents/DocumentUploadForm';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { ProgramDocumentEntry } from '../../types/programEngine';

type RouteP = RouteProp<{ ProgramDocumentUpload: { entry: ProgramDocumentEntry } }, 'ProgramDocumentUpload'>;

const ProgramDocumentUploadScreen: React.FC = () => {
  const { entry } = useRoute<RouteP>().params;
  const { slug, refresh } = useProgram();

  const onUpload = async (files: PickedFile[]) => {
    await programEngineService.uploadDocument(slug!, { document_type: entry.document_type, files });
    refresh();
  };

  return <DocumentUploadForm entry={entry} onUpload={onUpload} hint="Formatos: JPG, PNG, PDF, Word o video MP4/MOV (hasta 200MB)." />;
};

export default ProgramDocumentUploadScreen;
