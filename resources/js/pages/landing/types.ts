export type LandingSectionProps = {
    canRegister: boolean;
};

export type LandingProduct = {
    id: string;
    name: string;
    code: string;
    sku: string;
    price: number;
    description: string | null;
};
