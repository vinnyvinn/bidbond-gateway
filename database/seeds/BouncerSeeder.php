<?php

use Illuminate\Database\Seeder;

class BouncerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bouncer::allow('superadmin')->everything();


        $customer = Bouncer::role()->firstOrCreate(['name' => 'customer']);
        $agent = Bouncer::role()->firstOrCreate(['name' => 'agent']);
        $support = Bouncer::role()->firstOrCreate(['name' => 'support']);
        $relationship_manager = Bouncer::role()->firstOrCreate(['name' => 'relationship_manager']);
        $operations = Bouncer::role()->firstOrCreate(['name' => 'operations']);

        Bouncer::ability()->firstOrCreate(['name' => 'list-users']);
        Bouncer::ability()->firstOrCreate(['name' => 'list-roles']);
        $create_roles = Bouncer::ability()->firstOrCreate(['name' => 'create-roles']);
        Bouncer::ability()->firstOrCreate(['name' => 'attach-permissions']);
        $create_users = Bouncer::ability()->firstOrCreate(['name' => 'create-users']);
        Bouncer::ability()->firstOrCreate(['name' => 'delete-users']);
        Bouncer::ability()->firstOrCreate(['name' => 'update-user']);
        Bouncer::ability()->firstOrCreate(['name' => 'view-user']);
        $restore_user = Bouncer::ability()->firstOrCreate(['name' => 'restore-user']);
        $suspend_user = Bouncer::ability()->firstOrCreate(['name' => 'suspend-user']);

        $list_companies_owned = Bouncer::ability()->firstOrCreate(['name' => 'list-companies-owned']);
        $list_companies = Bouncer::ability()->firstOrCreate(['name' => 'list-companies']);
        $create_companies = Bouncer::ability()->firstOrCreate(['name' => 'create-companies']);
        $view_companies = Bouncer::ability()->firstOrCreate(['name' => 'view-companies']);
        $view_companies_owned = Bouncer::ability()->firstOrCreate(['name' => 'view-companies-owned']);
        $add_directors = Bouncer::ability()->firstOrCreate(['name' => 'add-directors']);
        $approve_companies = Bouncer::ability()->firstOrCreate(['name' => 'approve-companies']);
        $search_companies = Bouncer::ability()->firstOrCreate(['name' => 'search-companies']);
        $delete_companies = Bouncer::ability()->firstOrCreate(['name' => 'delete-companies']);
        $restore_companies = Bouncer::ability()->firstOrCreate(['name' => 'restore-companies']);
        $update_companies = Bouncer::ability()->firstOrCreate(['name' => 'update-companies']);

        $attach_company_users = Bouncer::ability()->firstOrCreate(['name' => 'attach-company-users']);
        $update_companies_limit = Bouncer::ability()->firstOrCreate(['name' => 'update-companies-limit']);

        $list_bidbonds_owned = Bouncer::ability()->firstOrCreate(['name' => 'list-bidbonds-owned']);
        $list_bidbonds = Bouncer::ability()->firstOrCreate(['name' => 'list-bidbonds']);
        $create_bidbonds = Bouncer::ability()->firstOrCreate(['name' => 'create-bidbonds']);
        $update_bidbonds = Bouncer::ability()->firstOrCreate(['name' => 'update-bidbonds']);

        $list_bidbond_templates = Bouncer::ability()->firstOrCreate(['name' => 'list-bidbond-templates']);
        $create_bidbond_templates = Bouncer::ability()->firstOrCreate(['name' => 'create-bidbond-templates']);
        $update_bidbond_templates = Bouncer::ability()->firstOrCreate(['name' => 'update-bidbond-templates']);
        Bouncer::ability()->firstOrCreate(['name' => 'delete-bidbond-templates']);
        $list_counterparties = Bouncer::ability()->firstOrCreate(['name' => 'list-counterparties']);
        $create_counterparties = Bouncer::ability()->firstOrCreate(['name' => 'create-counterparties']);
        $edit_counterparties = Bouncer::ability()->firstOrCreate(['name' => 'edit-counterparties']);
        Bouncer::ability()->firstOrCreate(['name' => 'delete-counterparties']);
        $list_bidbond_pricing = Bouncer::ability()->firstOrCreate(['name' => 'list-bidbond-pricing']);
        $create_bidbond_pricing = Bouncer::ability()->firstOrCreate(['name' => 'create-bidbond-pricing']);
        $edit_bidbond_pricing = Bouncer::ability()->firstOrCreate(['name' => 'edit-bidbond-pricing']);
        $delete_bidbond_pricing = Bouncer::ability()->firstOrCreate(['name' => 'delete-bidbond-pricing']);

        $list_payments = Bouncer::ability()->firstOrCreate(['name' => 'list-payments']);
        $list_payments_owned = Bouncer::ability()->firstOrCreate(['name' => 'list-payments-owned']);

        Bouncer::ability()->firstOrCreate(['name' => 'list-reports']);

        Bouncer::ability()->firstOrCreate(['name' => 'list-customers']);

        $payments_reports = Bouncer::ability()->firstOrCreate(['name' => 'payments-reports']);
        Bouncer::ability()->firstOrCreate(['name' => 'users-reports']);
        $bidbonds_reports = Bouncer::ability()->firstOrCreate(['name' => 'bidbonds-reports']);
        $companies_reports = Bouncer::ability()->firstOrCreate(['name' => 'companies-reports']);


        $view_commission_balance = Bouncer::ability()->firstOrCreate(['name' => 'view-commission-balance']);
        $list_commission = Bouncer::ability()->firstOrCreate(['name' => 'list-commission']);
        $list_commission_owned = Bouncer::ability()->firstOrCreate(['name' => 'list-commission-owned']);
        $view_wallet_balance = Bouncer::ability()->firstOrCreate(['name' => 'view-wallet-balance']);

        Bouncer::ability()->firstOrCreate(['name' => 'manage-settings']);


        $view_graphs = Bouncer::ability()->firstOrCreate(['name' => 'view-graphs']);


        //documents
        $download_documents = Bouncer::ability()->firstOrCreate(['name' => 'download-documents']);
        $upload_documents = Bouncer::ability()->firstOrCreate(['name' => 'upload-documents']);

        //quotes
        Bouncer::ability()->firstOrCreate(['name' => 'list-quotes']);
        Bouncer::ability()->firstOrCreate(['name' => 'list-quotes-owned']);

        //agents
        $onboard_agencies = Bouncer::ability()->firstOrCreate(['name' => 'onboard-agencies']);
        $list_agents = Bouncer::ability()->firstOrCreate(['name' => 'list-agents']);
        $list_company_revenues = Bouncer::ability()->firstOrCreate(['name' => 'list-company-revenues-reports']);
        $list_agents_owned = Bouncer::ability()->firstOrCreate(['name' => 'list-agents-owned']);
        $list_agent_companies = Bouncer::ability()->firstOrCreate(['name' => 'list-agent-companies']);
        Bouncer::ability()->firstOrCreate(['name' => 'list-agents-reports']);
        $list_agents_owned_reports = Bouncer::ability()->firstOrCreate(['name' => 'list-agents-owned-reports']);
        Bouncer::ability()->firstOrCreate(['name' => 'delete-agents']);
        $restore_agents = Bouncer::ability()->firstOrCreate(['name' => 'restore-agents']);
        $update_agent = Bouncer::ability()->firstOrCreate(['name' => 'update-agent']);
        $update_agents_owned = Bouncer::ability()->firstOrCreate(['name' => 'update-agents-owned']);

        Bouncer::ability()->firstOrCreate(['name' => 'marketing']);

        Bouncer::forbid('superadmin')->to([
            $list_companies_owned, $list_bidbonds_owned, $view_companies_owned,
            $list_agents_owned, $list_agents_owned_reports,
            $list_commission_owned, $update_agents_owned,
            $list_payments_owned,
            $restore_user,$suspend_user,$create_users,$create_roles,
            $add_directors,
            $create_companies,$view_companies,$update_bidbonds
        ]);

        Bouncer::allow($customer)->to([
            $list_companies_owned, $create_companies, $view_companies_owned,
            $add_directors, $download_documents,
            $list_bidbonds_owned, $create_bidbonds,
            $list_bidbond_templates,
            $list_counterparties,
            $view_wallet_balance]);

        Bouncer::allow($support)->to([
            $create_companies, $view_companies, $list_companies, $approve_companies, $search_companies, $delete_companies, $update_companies,
            $list_bidbonds, $restore_agents, $update_agent,
            $add_directors, $list_bidbond_templates, $update_bidbond_templates,
            $create_counterparties, $edit_counterparties, $update_bidbonds,
            $list_payments,
            $suspend_user, $restore_user,
            $download_documents, $upload_documents]);

        Bouncer::allow($operations)->to([
            $list_companies, $view_companies, $approve_companies, $search_companies, $update_companies_limit, $update_companies,
            $list_bidbonds, $list_bidbond_templates, $update_bidbond_templates, $create_bidbond_templates,
            $list_counterparties, $create_counterparties, $edit_counterparties,
            $list_payments,
            $payments_reports, $bidbonds_reports, $companies_reports, $view_graphs,
            $suspend_user,
            $restore_companies,
            $download_documents, $upload_documents,
            $onboard_agencies, $list_agents, $list_agent_companies,
            $list_commission,
            $list_bidbond_pricing, $create_bidbond_pricing, $edit_bidbond_pricing, $delete_bidbond_pricing
        ]);

        Bouncer::allow($relationship_manager)->to([
            $list_companies, $view_companies, $search_companies,
            $list_bidbonds, $list_bidbond_templates, $update_bidbond_templates,
            $create_counterparties, $edit_counterparties,
            $list_payments, $payments_reports,
            $bidbonds_reports, $view_graphs,
            $suspend_user,
            $download_documents, $upload_documents,
            $view_commission_balance, $list_commission_owned,
            $onboard_agencies]);

        Bouncer::allow($agent)->to([
            $list_companies_owned, $create_companies, $view_companies_owned,
            $list_agents_owned,
            $list_bidbonds_owned, $create_bidbonds,
            $list_bidbond_templates,
            $list_counterparties,
            $list_payments_owned, $view_wallet_balance]);


    }
}
