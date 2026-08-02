export interface MultipleChoiceOption {
    id: string;
    text: string;
}

export interface MultipleChoicePayload {
    options: MultipleChoiceOption[];
    correct_option_id: string;
}
