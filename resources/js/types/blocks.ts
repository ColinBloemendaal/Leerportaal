export interface RichTextContent {
    html: string;
}

export interface ImageContent {
    url: string;
    alt: string;
    caption: string | null;
}

export interface VideoEmbedContent {
    url: string;
    provider: string | null;
}

export interface FileDownloadContent {
    url: string;
    label: string;
}

export interface EmbedContent {
    url: string;
}

export type CalloutVariant = 'info' | 'warning' | 'danger' | 'success';

export interface CalloutContent {
    text: string;
    variant: CalloutVariant;
}
