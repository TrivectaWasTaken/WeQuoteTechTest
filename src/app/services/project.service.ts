import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { Project, Stats } from '../models/project.model';

@Injectable({
  providedIn: 'root'
})
export class ProjectService {
  private apiUrl = 'http://wequotebackend.test/api'; // Standard PHP setup

  constructor(private http: HttpClient) { }

  getProjects(customerId?: string): Observable<any[]> {
    let url = `${this.apiUrl}/projects`;
    if (customerId) {
        url = `${this.apiUrl}/customers/${customerId}/projects`;
    }
    return this.http.get<any[]>(url);
  }

  getInvoices(projectId: string): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/invoices/${projectId}`);
  }

  getQuotes(projectId: string): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/quotes/${projectId}`);
  }

  getStats(organisationId: string = '1', customerId?: string): Observable<Stats> {
    let url = `${this.apiUrl}/stats/${organisationId}`;
    if (customerId) {
        url += `?customer_id=${customerId}`;
    }
    return this.http.get<Stats>(url);
  }

  getOrganisation(id: string = '1'): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/organisation/${id}`);
  }

  getProject(id: number): Observable<any> {
    // We can filter by ID in the projects.php or create a dedicated one.
    // Let's use projects.php?id=... if supported or just filter the list for now.
    // Better: update projects.php to handle id.
    return this.http.get<any>(`${this.apiUrl}/projects/${id}`);
  }
}
