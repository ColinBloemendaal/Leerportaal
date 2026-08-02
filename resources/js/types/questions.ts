export interface MultipleChoiceOption {
    id: string;
    text: string;
}

export interface MultipleChoicePayload {
    options: MultipleChoiceOption[];
    correct_option_id: string;
}

export interface MultipleResponseOption {
    id: string;
    text: string;
}

export interface MultipleResponsePayload {
    options: MultipleResponseOption[];
    correct_option_ids: string[];
}
