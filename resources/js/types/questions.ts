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

export interface TrueFalsePayload {
    correct_answer: boolean;
}

export type OpenShortMatchMode = 'exact' | 'contains' | 'regex';

export interface OpenShortPayload {
    match_mode: OpenShortMatchMode;
    case_sensitive: boolean;
    acceptable_answers: string[];
}

export interface EssayRubricCriterion {
    criterion: string;
    points: number;
}

export interface EssayPayload {
    rubric: EssayRubricCriterion[];
    min_words: number | null;
    max_words: number | null;
}

export interface MatchingPair {
    id: string;
    left: string;
    right: string;
}

export interface MatchingPayload {
    pairs: MatchingPair[];
}
