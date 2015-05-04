ANDS-Registry-Core
==================

Repository of Online Services (currently maintained by the Australian National Data Service) http://services.ands.org.au/

### ANDS Repository Structure

The ANDS Online Services software codebase is structured in a number of seperate software repositories, isolating different areas of functionality which might reasonably be expected to be deployed individually:

- (this repository) `au-research/ANDS-Registry-Core` - the core PHP codebase which includes a metadata registry, front-end portal and access management system
- `au-research/ANDS-Registry-Contrib` - non-core addons including CMS editor, widget libraries, identifier management front-end and other self-contained community-sourced contributions.
- `au-research/ANDS-Harvester` - a Java-based Tomcat web application used to schedule and harvest metadata from remote providers (over HTTP and OAI-PMH).
- `au-research/ANDS-PIDS-Service` - a Java-based Tomcat web application which provides an API layer implemented around the CNRI Handle service.
- `au-research/ANDS-RIFCS-API` - a Java library which provides a wrapper around the DOM methods required to manipulate and produce RIFCS documents.

### Installation and Support

- [Registry Installation Notes](https://researchdata.ands.org.au/developers/documentation/registry)
- [Community Online Support Forum](http://developers.ands.org.au)

### Recent Changelogs
- [Release 15.1](https://github.com/au-research/ANDS-Online-Services/wiki/Release15.1Changelog) (May 2015)
- [Release 15](https://github.com/au-research/ANDS-Online-Services/wiki/Release15Changelog) (Apr 2015)
- [Release 14](https://github.com/au-research/ANDS-Online-Services/wiki/Release14Changelog) (Dec 2014)
- [Release 13.3](https://github.com/au-research/ANDS-Online-Services/wiki/Release13.3Changelog) (Oct 2014)
- [Release 13](https://github.com/au-research/ANDS-Online-Services/wiki/Release13Changelog) (July 2014)
- [Release 12](https://github.com/au-research/ANDS-Online-Services/wiki/Release12Changelog) (March 2014)
- [Release 11.1](https://github.com/au-research/ANDS-Online-Services/wiki/Release11.1changelog) (December 2013)
- [Release 11](https://github.com/au-research/ANDS-Online-Services/wiki/Release11Changelog) (November 2013)
- [Release 10.3](https://github.com/au-research/ANDS-Online-Services/wiki/Release-10.3-changelog) (October 2013)
- [Release 10.2](https://github.com/au-research/ANDS-Online-Services/wiki/Release-10.2-changelog) (September 2013)
- [Release 10.1](https://github.com/au-research/ANDS-Online-Services/wiki/Release-10.1-changelog) (November 2013)


### License Terms
Unless otherwise specified, all ANDS Online Services software is Copyright 2009 The Australian National University and licensed under the Apache License version 2.0 (http://www.apache.org/licenses/LICENSE-2.0).
Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
