export type PasswordForm = {
  current_password: string;
  password: string;
  password_confirmation: string;
};

export type UserProfile = {
  name: string;
  email: string;
  created_at: string;
  email_verified_at: string | null;
  profile_picture?: string;
  profile_banner?: string;
};

export type PortfolioGrowth = {
  labels: string[];
  data: number[];
  raw: {
    date: string;
    value: number;
    timestamp: number;
  }[];
};

export type PortfolioDistribution = {
  labels: string[];
  data: number[];
  colors: string[];
  raw: {
    crypto_name: string;
    crypto_symbol: string;
    value: number;
    percentage: number;
    quantity: string;
  }[];
};

export type PortfolioStats = {
  total_invested: number;
  current_value: number;
  total_profit: number;
  profit_percentage: number;
  total_transactions: number;
};

export type PortfolioData = {
  stats: PortfolioStats;
  growth: PortfolioGrowth;
  distribution: PortfolioDistribution;
};

export type PortfolioResponse = {
  success: boolean;
  data: PortfolioData;
};