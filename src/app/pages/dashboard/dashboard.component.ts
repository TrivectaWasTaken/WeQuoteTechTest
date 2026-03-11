import {Component, OnInit, signal, OnDestroy} from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { Project, Stats } from '../../models/project.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit, OnDestroy {
  projects = signal<Project[]>([]);
  stats = signal<Stats|null>(null);
  organisation = signal<any>(null);
  private subscriptions = new Subscription();

  constructor(
    private projectService: ProjectService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(this.route.params.subscribe(params => {
      const customerId = params['id'];
      this.loadDashboardData(customerId);
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  getSegmentOffset(index: number, payments: any[]): number {
    const total = payments.reduce((sum, p) => sum + Number(p.total_paid), 0);
    if (total === 0) return 0;
    const sumBefore = payments.slice(0, index).reduce((sum, p) => sum + Number(p.total_paid), 0);
    // DASHARRAY starts at the right (3 o'clock) if offset is 0.
    // BUT we have -offset + 25 in the HTML, which rotates it by 25% (90 degrees).
    // So it starts at the top (12 o'clock).
    return (sumBefore / total) * 100;
  }

  getSegmentWidth(index: number, payments: any[]): number {
    const total = payments.reduce((sum, p) => sum + Number(p.total_paid), 0);
    if (total === 0) return 0;
    return (Number(payments[index].total_paid) / total) * 100;
  }

  getSegmentColor(index: number): string {
    const colors = ['#0f766e', '#2dd4bf', '#000a2d', '#5eead4', '#ccfbf1'];
    return colors[index % colors.length];
  }

  getSegmentLabelX(index: number, payments: any[]): number {
    const total = payments.reduce((sum, p) => sum + Number(p.total_paid), 0);
    if (total === 0) return 18;
    const sumBefore = payments.slice(0, index).reduce((sum, p) => sum + Number(p.total_paid), 0);
    const midPercentage = ((sumBefore + Number(payments[index].total_paid) / 2) / total) * 100;
    // Angle needs to match SVG coordinate system:
    // midPercentage: 0 -> 100
    // SVG DashOffset + 25 starts at top (12 o'clock = -90 degrees).
    // Clockwise: 0% -> -90 deg, 25% -> 0 deg, 50% -> 90 deg, 75% -> 180 deg
    const angle = (midPercentage / 100) * 360 - 90;
    const radius = 18.5; // Outside the 6-width stroke (r=13 + stroke/2 = 16)
    return 18 + radius * Math.cos(angle * (Math.PI / 180));
  }

  getSegmentLabelY(index: number, payments: any[]): number {
    const total = payments.reduce((sum, p) => sum + Number(p.total_paid), 0);
    if (total === 0) return 18;
    const sumBefore = payments.slice(0, index).reduce((sum, p) => sum + Number(p.total_paid), 0);
    const midPercentage = ((sumBefore + Number(payments[index].total_paid) / 2) / total) * 100;
    const angle = (midPercentage / 100) * 360 - 90;
    const radius = 18.5; // Outside the 6-width stroke (r=13 + stroke/2 = 16)
    return 18 + radius * Math.sin(angle * (Math.PI / 180));
  }

  loadDashboardData(customerId?: string): void {
    if (customerId) {
        this.subscriptions.add(this.projectService.getCustomer(customerId).subscribe({
            next: (customer) => {
                const orgId = customer.organisation_id || '1';
                this.organisation.set(customer.organisation || { company_name: 'LCR Integrated Systems (STAGING)' });

                this.subscriptions.add(this.projectService.getProjects(customerId).subscribe(data => this.projects.set(data)));
                this.subscriptions.add(this.projectService.getStats(orgId.toString(), customerId).subscribe(data => this.stats.set(data)));
            },
            error: (err) => {
                console.error('Failed to fetch customer', err);
                this.loadDefaultDashboard(customerId);
            }
        }));
    } else {
        this.loadDefaultDashboard();
    }
  }

  private loadDefaultDashboard(customerId?: string): void {
    this.subscriptions.add(this.projectService.getOrganisation('1').subscribe({
        next: (data) => this.organisation.set(data || { company_name: 'LCR Integrated Systems (STAGING)' }),
        error: () => this.organisation.set({ company_name: 'LCR Integrated Systems (STAGING)' })
    }));
    this.subscriptions.add(this.projectService.getStats('1', customerId).subscribe(data => this.stats.set(data)));
    this.subscriptions.add(this.projectService.getProjects(customerId).subscribe(data => {
        this.projects.set(data);
        if (data && data.length > 0 && (!this.organisation() || this.organisation()?.company_name === 'LCR Integrated Systems (STAGING)')) {
             // If we have projects, try to update the organisation from the first one
             const firstProject = data[0];
             const orgId = firstProject?.organisation_id;
             if (orgId && orgId.toString() !== '1') {
                 this.projectService.getOrganisation(orgId.toString()).subscribe(org => {
                     if (org) this.organisation.set(org);
                 });
             }
        }
    }));
  }
}
