export interface Project {
  id: number;
  name: string;
  customer_id: number;
  customer_name: string;
  customer_email?: string;
  customer_phone?: string;
  quote_count: number;
  invoice_count: number;
  created_datetime: string;
  modified_datetime: string;
  status: string;
  net_total: number;
  user_name: string;
  address_line_1?: string;
  address_line_2?: string;
  posttown?: string;
  post_town?: string;
  postcode?: string;
  paid_total: number;
  outstanding_total?: number;
  draft_total?: number;
  not_invoiced_total?: number;
}

export interface Stats {
    total_projects: number;
    total_paid: number;
    outstanding_invoices: number;
    remaining_to_pay: number;
    new_quotes: number;
    new_invoices: number;
    last_payments?: Array<{ month_year: string, total_paid: number }>;
    current_user?: { id: number, name: string };
}
